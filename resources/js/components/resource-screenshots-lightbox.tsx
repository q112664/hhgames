import Lightbox from 'yet-another-react-lightbox';
import DownloadPlugin from 'yet-another-react-lightbox/plugins/download';
import Zoom from 'yet-another-react-lightbox/plugins/zoom';
import 'yet-another-react-lightbox/styles.css';

export type ResourceScreenshotsLightboxSlide = {
    src: string;
    alt: string;
    download?: {
        url: string;
        filename: string;
    };
};

export default function ResourceScreenshotsLightbox({
    index,
    onClose,
    onView,
    slides,
}: {
    index: number;
    onClose: () => void;
    onView: (index: number) => void;
    slides: ResourceScreenshotsLightboxSlide[];
}) {
    return (
        <Lightbox
            open={index >= 0}
            index={index >= 0 ? index : 0}
            close={onClose}
            on={{ view: ({ index: nextIndex }) => onView(nextIndex) }}
            controller={{ closeOnBackdropClick: true }}
            plugins={[DownloadPlugin, Zoom]}
            styles={{
                container: {
                    backgroundColor: 'rgba(15, 23, 42, 0.72)',
                },
            }}
            zoom={{
                maxZoomPixelRatio: 2.5,
                scrollToZoom: true,
            }}
            slides={slides}
        />
    );
}
