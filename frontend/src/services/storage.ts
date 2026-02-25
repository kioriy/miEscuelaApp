import { Storage, Drivers } from '@ionic/storage';

let resolveReady: any;
export const storageReady = new Promise((resolve) => {
    resolveReady = resolve;
});

// Initialize the storage
export const storage = new Storage({
    name: 'educontrol_db',
    driverOrder: [Drivers.IndexedDB, Drivers.LocalStorage]
});

export const initStorage = async () => {
    try {
        await storage.create();
        console.log('📦 Ionic Storage Initialized');
        resolveReady();
    } catch (error) {
        console.error('❌ Error initializing Ionic Storage:', error);
    }
};

export default storage;
