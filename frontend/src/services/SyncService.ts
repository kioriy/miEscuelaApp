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
                const { students, authorized_persons, teachers } = res.data.data;

                // 1. Guardar alumnos en Dexie (Upsert)
                if (res.data.data.debug) {
                    console.log('[Sync] Debug Backend:', res.data.data.debug);
                }

                if (students.length > 0) {
                    const schoolsFound = [...new Set(students.map((s: any) => s.school_id))];
                    console.log(`[Sync] Recibidos ${students.length} alumnos de las escuelas: ${schoolsFound.join(', ')}`);
                    await db.students.bulkPut(students);
                }

                // 2. Guardar profesores en Dexie (Upsert)
                if (teachers && teachers.length > 0) {
                    console.log(`[Sync] Recibidos ${teachers.length} profesores.`);
                    await db.teachers.bulkPut(teachers);
                }

                // 3. Guardar última fecha de sincronización
                await storage.set('last_students_sync', res.data.timestamp);

                return { success: true, count: students.length + (teachers?.length || 0) };
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
            
            const pendingTeacherLogs = await db.teacherAttendanceLogs
                .where('sync_status')
                .equals('pending')
                .toArray();

            if (pendingLogs.length === 0 && pendingTeacherLogs.length === 0) return { success: true, count: 0 };

            const res = await api.post('/sync/monitor/push', {
                logs: pendingLogs,
                teacher_logs: pendingTeacherLogs
            });

            if (res.data.success) {
                // Marcar como sincronizados localmente (Alumnos)
                if (pendingLogs.length > 0) {
                    const ids = pendingLogs.map(log => log.id).filter(id => id !== undefined) as number[];
                    await db.attendanceLogs.bulkUpdate(ids.map(id => ({
                        key: id,
                        changes: { sync_status: 'synced' }
                    })));
                }

                // Marcar como sincronizados localmente (Profesores)
                if (pendingTeacherLogs.length > 0) {
                    const tids = pendingTeacherLogs.map(log => log.id).filter(id => id !== undefined) as number[];
                    await db.teacherAttendanceLogs.bulkUpdate(tids.map(id => ({
                        key: id,
                        changes: { sync_status: 'synced' }
                    })));
                }

                console.log(`[Sync] ${pendingLogs.length} asistencias y ${pendingTeacherLogs.length} de profesores subidas.`);
                return { success: true, count: pendingLogs.length + pendingTeacherLogs.length };
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
