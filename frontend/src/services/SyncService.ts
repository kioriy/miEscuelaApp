import api from './api';
import { db, LocalStudent } from './db';
import { storage } from './storage';

export class SyncService {
    /**
     * PULL: Descarga alumnos y autorizados modificados desde la última sincronización.
     */
    static async pullStudents() {
        try {
            const lastSync = await storage.get('last_students_sync') || '2000-01-01 00:00:00';
            const kioskConfig = await storage.get('kiosk_config');
            const kioskId = kioskConfig?.id;

            const res = await api.get('/sync/monitor/pull', {
                params: {
                    last_sync: lastSync,
                    kiosk_id: kioskId
                }
            });

            if (res.data.success) {
                const { students, authorized_persons } = res.data.data;

                // 1. Guardar alumnos en Dexie (Upsert)
                if (res.data.data.debug) {
                    console.log('[Sync] Debug Backend:', res.data.data.debug);
                }

                if (students.length > 0) {
                    const schoolsFound = [...new Set(students.map((s: any) => s.school_id))];
                    console.log(`[Sync] Recibidos ${students.length} alumnos de las escuelas: ${schoolsFound.join(', ')}`);
                    await db.students.bulkPut(students);
                } else {
                    console.log('[Sync] No hay alumnos nuevos para descargar.');
                }

                // 2. Guardar última fecha de sincronización
                await storage.set('last_students_sync', res.data.timestamp);

                return { success: true, count: students.length };
            }
        } catch (error) {
            console.error('[Sync Pull Error]', error);
            return { success: false, error };
        }
    }

    /**
     * PUSH: Sube los logs de asistencia pendientes de sincronización.
     */
    static async pushAttendance() {
        try {
            const pendingLogs = await db.attendanceLogs
                .where('sync_status')
                .equals('pending')
                .toArray();

            if (pendingLogs.length === 0) return { success: true, count: 0 };

            const res = await api.post('/sync/monitor/push', {
                logs: pendingLogs
            });

            if (res.data.success) {
                // Marcar como sincronizados localmente
                const ids = pendingLogs.map(log => log.id).filter(id => id !== undefined) as number[];
                await db.attendanceLogs.bulkUpdate(ids.map(id => ({
                    key: id,
                    changes: { sync_status: 'synced' }
                })));

                console.log(`[Sync] ${pendingLogs.length} asistencias subidas al servidor.`);
                return { success: true, count: pendingLogs.length };
            }
        } catch (error) {
            console.error('[Sync Push Error]', error);
            return { success: false, error };
        }
    }

    /**
     * Orquestador de sincronización completa.
     */
    static async syncAll() {
        console.log('[Sync] Iniciando sincronización completa...');
        const push = await this.pushAttendance();
        const pull = await this.pullStudents();
        return { push, pull };
    }
}
