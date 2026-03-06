import Dexie, { Table } from 'dexie';

export interface LocalStudent {
    id: number;
    school_id: number;
    enrollment_code: string;
    first_name: string;
    last_name: string;
    grade: string;
    group_letter: string;
    photo_url?: string;
    photo_hash?: string;
    is_active: number;
    last_sync_at: string;
}

export interface LocalAttendanceLog {
    id?: number; // IndexedDB ID (local only)
    student_id: number;
    scanned_at: string;
    type: 'in' | 'out';
    sync_status: 'pending' | 'synced';
    kiosk_id?: number;
}

export class AppDatabase extends Dexie {
    students!: Table<LocalStudent>;
    attendanceLogs!: Table<LocalAttendanceLog>;

    constructor() {
        super('EduControlDB');
        this.version(1).stores({
            students: 'id, enrollment_code, [grade+group_letter]',
            attendanceLogs: '++id, student_id, sync_status, scanned_at'
        });
    }

    async clear() {
        await this.students.clear();
        await this.attendanceLogs.clear();
    }
}

export const db = new AppDatabase();
