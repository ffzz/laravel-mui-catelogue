import ContentList from '@/components/CatalogueContent';
import ContentLayout from '@/layouts/ContentLayout';

const ContentPage: React.FC = () => {
    return (
        <ContentLayout fullWidth title="Content Catalogue">
            <ContentList />
        </ContentLayout>
    );
};

export default ContentPage;
