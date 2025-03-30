// Define Content interface based on PHP Content.php
interface ContentItem {
    id: number;
    fullname: string;
    summary: string;
    image: string | null;
    contentType: string;
    url: string;
    badge: string | null;
    completionStatus: string | null;
    programs: unknown[] | null;
    category: unknown[] | null;
    tags: string[] | null;
    customfields: Record<string, unknown> | null;
    cost: number;
    duration: string;
    timeCreated: string;
    timeModified: string;
    contentStatus: string;
    paymentCost: unknown;
    // Additional properties from transform method
    type?: string;
    has_image?: boolean;
    formatted_date?: {
        created: string;
        modified: string;
    };
}

interface ContentLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    fullWidth?: boolean;
    title?: string;
    contentTypeFilter?: string;
}

// Define the API response structure
interface ContentListResponse {
    success: boolean;
    items: ContentItem[];
    metadata: {
        currentPage: number;
        totalPages: number;
        totalItems: number;
        nextPageUrl?: string | null;
        previousPageUrl?: string | null;
    };
}

// Define the refresh cache response structure
interface RefreshCacheResponse {
    success: boolean;
    message: string;
}

type ContentTypeColor = 'primary' | 'secondary' | 'default' | 'error' | 'info' | 'success' | 'warning';

export type { ContentItem, ContentLayoutProps, ContentListResponse, ContentTypeColor, RefreshCacheResponse };
