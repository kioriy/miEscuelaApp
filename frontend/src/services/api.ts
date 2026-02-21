import axios from 'axios';
import { storage } from './storage';

// Crea una instancia de Axios pre-configurada
const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api', // Reemplazar por URL en Producción
    timeout: 15000,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// Interceptor de solicitudes para inyectar automáticamente el Bearer Token guardado en local (Ionic Storage)
api.interceptors.request.use(
    async (config) => {
        // Intentar leer el token
        const val = await storage.get('auth_token');
        if (val) {
            config.headers.Authorization = `Bearer ${val}`;
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Interceptor para manejo global de errores
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            // Opcional: Desloguear al usuario en un futuro
            console.warn('⚠️ No autorizado, la sesión podría estar expirada.');
        }
        return Promise.reject(error);
    }
);

export default api;
