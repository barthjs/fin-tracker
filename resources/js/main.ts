import { syncThemeColor } from '@/lib/browser/themeColor';
import { registerServiceWorker } from '@/lib/pwa/serviceWorker';
import { syncStickyTableTops } from '@/lib/tables/stickyTables';

syncStickyTableTops();
syncThemeColor();
registerServiceWorker();
