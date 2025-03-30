import DOMPurify from 'dompurify';

// get status color
const getStatusColor = (status: string) => {
    if (status === 'Active' || status === 'active') return 'success';
    if (status === 'Pending' || status === 'pending') return 'warning';
    return 'error';
};

const removeSpecialSymbols = (name: string) => {
    return name.trim().replace(/^[_\-+.]+/, '');
};

const formattedDate = (date: string) => {
    // if date is not a string, return empty string
    if (!date || typeof date !== 'string') return '';

    return new Date(date).toLocaleDateString('en-AU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// safe render html content
const createSafeHtml = (html: string) => {
    const cleanHtml = DOMPurify.sanitize(html);
    return { __html: cleanHtml };
};

export { createSafeHtml, formattedDate, getStatusColor, removeSpecialSymbols };
