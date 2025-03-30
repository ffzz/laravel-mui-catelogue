/**
 * Content type enumeration
 * Must match PHP ContentType enum
 */
export enum ContentType {
    COURSE = 'course',
    LIVE_LEARNING = 'live learning',
    RESOURCE = 'resource',
    VIDEO = 'video',
    PROGRAM = 'program',
    PAGE = 'page',
    PARTNERED_CONTENT = 'partnered content',
}

/**
 * Get all content type values as an array
 */
export const getContentTypeValues = (): string[] => {
    return Object.values(ContentType);
};
