import ContentFilter from '@/components/contentPage/ContentFilter';
import ContentGrid from '@/components/contentPage/ContentGrid';
import Intro from '@/components/contentPage/Intro';
import SimplePagination from '@/components/contentPage/SimplePagination';
import ErrorDisplay from '@/components/ErrorDisplay';
import LoadingContent from '@/components/LoadingContent';
import NoContentDisplay from '@/components/NoContentDisplay';
import useContentTypeFilter from '@/hooks/useContentTypeFilter';
import { useApiCreate, useApiQuery } from '@/lib/api/hooks';
import { ContentListResponse, RefreshCacheResponse } from '@/types/content';
import { Container, SelectChangeEvent } from '@mui/material';
import React, { useState } from 'react';

const ContentList: React.FC = () => {
    const [noCache, setNoCache] = useState<boolean>(false);
    const [refreshing, setRefreshing] = useState<boolean>(false);
    const [page, setPage] = useState<number>(1);

    const { contentType, contentTypeColor, contentTypeOptions = [], onContentTypeChange } = useContentTypeFilter();

    const handleContentTypeChange = (event: SelectChangeEvent<string>) => {
        onContentTypeChange(event.target.value);
        setPage(1); // Reset to first page when changing content type
    };

    // Use our custom hook to fetch content list
    const { data, isLoading, isError, error, refetch } = useApiQuery<ContentListResponse>(
        ['content', contentType, String(page), String(noCache)],
        '/content',
        {
            contentType: contentType || undefined,
            page,
            perPage: 10,
            noCache: noCache || undefined,
        },
    );

    const { items: contents = [], metadata = { currentPage: 1, totalPages: 1 } } = data?.data || {};

    // Use API create hook for refreshing cache
    const refreshCacheMutation = useApiCreate<RefreshCacheResponse>({
        onSuccess: () => {
            refetch();
            setNoCache(true);
            setTimeout(() => setRefreshing(false), 1000);
        },
        onError: (error) => {
            console.error('Error refreshing cache:', error);
            setRefreshing(false);
        },
    });

    const handleRefreshCache = () => {
        setRefreshing(true);

        // Use the mutation hook to refresh cache
        refreshCacheMutation.mutate({
            url: '/content/refresh-cache',
            data: contentType ? { contentType } : undefined,
        });
    };

    return (
        <Container maxWidth="lg" sx={{ p: 4, display: 'flex', flexDirection: 'column', gap: 4 }}>
            {/* page intro text */}
            <Intro />

            <Container>
                {/* content filter */}
                <ContentFilter
                    {...{
                        noCache,
                        setNoCache,
                        handleRefreshCache,
                        setPage,
                        refreshing,
                        contentTypeOptions,
                        handleContentTypeChange,
                        contentType,
                        contentTypeColor,
                    }}
                />
                {/* content list */}
                {isLoading ? (
                    <LoadingContent />
                ) : isError ? (
                    <ErrorDisplay error={error} />
                ) : contents.length === 0 ? (
                    <NoContentDisplay />
                ) : (
                    <>
                        <ContentGrid contents={contents} contentTypeColor={contentTypeColor} />
                        {metadata && <SimplePagination page={page} setPage={setPage} metadata={metadata} />}
                    </>
                )}
            </Container>
        </Container>
    );
};

export default ContentList;
