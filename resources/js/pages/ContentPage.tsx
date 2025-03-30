import CatalogueContent from '@/components/contentPage/CatalogueContent';
import ContentLayout from '@/layouts/ContentLayout';

const ContentPage: React.FC = () => {
    return (
        <ContentLayout fullWidth title="Laravel MUI Catalogue">
            <CatalogueContent />
        </ContentLayout>
    );
};

export default ContentPage;
