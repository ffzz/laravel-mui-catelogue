import ContentList from '@/components/contentPage/CatalogueContent';
import ContentLayout from '@/layouts/ContentLayout';

const ContentPage: React.FC = () => {
    return (
        <ContentLayout fullWidth title="Laravel MUI Catalogue">
            <ContentList />
        </ContentLayout>
    );
};

export default ContentPage;
