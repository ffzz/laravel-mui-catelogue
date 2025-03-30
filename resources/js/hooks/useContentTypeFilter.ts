import { ContentType } from '@/enums/ContentType';
import { ContentTypeColor } from '@/types/content';
import { useState } from 'react';
const useContentTypeFilter = () => {
    const [contentType, setContentType] = useState<string>('');
    const [contentTypeColor, setContentTypeColor] = useState<ContentTypeColor>('primary');

    // Function to update content type filter
    const onContentTypeChange = (type: string) => {
        setContentType(type);
        setContentTypeColor(getContentTypeColor(type));
    };

    // Content type options
    const contentTypeOptions = [
        { value: '', label: 'All Types' },
        { value: ContentType.COURSE, label: 'Course' },
        { value: ContentType.LIVE_LEARNING, label: 'Live Learning' },
        { value: ContentType.RESOURCE, label: 'Resource' },
        { value: ContentType.VIDEO, label: 'Video' },
        { value: ContentType.PROGRAM, label: 'Program' },
        { value: ContentType.PAGE, label: 'Page' },
        { value: ContentType.PARTNERED_CONTENT, label: 'Partnered Content' },
    ];

    return { contentType, contentTypeColor, onContentTypeChange, contentTypeOptions };
};

// Function to determine color based on content type
const getContentTypeColor = (contentType: string): 'primary' | 'secondary' | 'default' | 'error' | 'info' | 'success' | 'warning' => {
    switch (contentType.toLowerCase()) {
        case ContentType.COURSE:
            return 'primary';
        case ContentType.LIVE_LEARNING:
            return 'secondary';
        case ContentType.RESOURCE:
            return 'success';
        case ContentType.VIDEO:
            return 'info';
        case ContentType.PROGRAM:
            return 'warning';
        case ContentType.PAGE:
            return 'default';
        case ContentType.PARTNERED_CONTENT:
            return 'error';
        default:
            return 'primary';
    }
};

export default useContentTypeFilter;
