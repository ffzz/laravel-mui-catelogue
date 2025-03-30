import DOMPurify from 'dompurify';

// 根据contentStatus确定状态点颜色
const getStatusColor = (status: string) => {
    if (status === 'Active' || status === 'active') return 'success';
    if (status === 'Pending' || status === 'pending') return 'warning';
    return 'error';
};

const removeSpecialSymbols = (name: string) => {
    return name.trim().replace(/^[_\-+.]+/, '');
};

const formattedDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-AU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// 安全渲染HTML内容
const createSafeHtml = (html: string) => {
    const cleanHtml = DOMPurify.sanitize(html);
    return { __html: cleanHtml };
};

export { createSafeHtml, formattedDate, getStatusColor, removeSpecialSymbols };
