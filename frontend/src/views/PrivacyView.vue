<template>
  <ion-page>
    <ion-content :fullscreen="true" class="bg-gray-50">
      <div class="min-h-screen bg-gray-50 flex flex-col py-12 px-6 sm:px-12 lg:px-24">
        <div class="max-w-4xl mx-auto w-full">
          
          <!-- Header with Back Button -->
          <div class="flex items-center justify-between mb-12">
            <div class="flex items-center gap-4">
              <img src="@/assets/images/logo.png" alt="miEscuelaApp" class="w-12 h-12 object-contain" />
              <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight leading-none mb-1">Aviso de Privacidad</h1>
                <p class="text-gray-500 font-semibold text-sm">miEscuelaApp — Seguridad y Confianza</p>
              </div>
            </div>
            <button @click="$router.back()" class="bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-6 rounded-xl text-sm shadow-sm hover:bg-gray-50 flex items-center gap-2 transition-all active:scale-95">
              <ion-icon :icon="arrowBackOutline" class="text-lg"></ion-icon>
              Volver
            </button>
          </div>

          <!-- Content Card -->
          <div class="bg-white rounded-[32px] shadow-sm border border-gray-100 p-8 sm:p-12 space-y-10 leading-relaxed text-gray-600">
            
            <section>
              <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                <div class="w-1.5 h-6 bg-brand-blue rounded-full"></div>
                1. Información que Recolectamos
              </h2>
              <p>
                En <strong>miEscuelaApp</strong>, recolectamos información personal necesaria para la gestión educativa y de seguridad, incluyendo: nombres, correos electrónicos, datos de asistencia y fotografías de perfil de estudiantes y personal autorizado.
              </p>
            </section>

            <section>
              <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                <div class="w-1.5 h-6 bg-brand-blue rounded-full"></div>
                2. Uso de la Información
              </h2>
              <p>
                Su información se utiliza exclusivamente para:
              </p>
              <ul class="list-disc pl-6 mt-3 space-y-2">
                <li>Validar el acceso de estudiantes y personal en los kioscos de asistencia.</li>
                <li>Notificar a los padres y tutores sobre entradas y salidas en tiempo real.</li>
                <li>Generar reportes estadísticos para la administración escolar.</li>
                <li>Facilitar la comunicación interna entre la institución y las familias.</li>
              </ul>
            </section>

            <section>
              <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                <div class="w-1.5 h-6 bg-brand-blue rounded-full"></div>
                3. Seguridad y Protección
              </h2>
              <p>
                Implementamos medidas de seguridad técnicas y administrativas de primer nivel para proteger sus datos contra acceso no autorizado, pérdida o alteración. Utilizamos cifrado SSL y servidores seguros para garantizar que su información esté siempre protegida.
              </p>
            </section>

            <section>
              <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                <div class="w-1.5 h-6 bg-brand-blue rounded-full"></div>
                4. Sus Derechos
              </h2>
              <p>
                Usted tiene derecho a acceder, rectificar o cancelar el uso de sus datos personales en cualquier momento. Para ejercer estos derechos, puede ponerse en contacto con la administración de su institución educativa.
              </p>
            </section>

            <section class="pt-8 border-t border-gray-50">
              <p class="text-sm font-medium text-gray-400">
                Última actualización: 17 de Marzo de 2026.
              </p>
            </section>

          </div>
          
          <!-- Bottom Logo Footer -->
          <div class="mt-12 text-center text-gray-400 font-medium">
             <div class="flex items-center justify-center gap-2 mb-4 opacity-50">
                <img src="@/assets/images/logo.png" alt="miEscuelaApp" class="w-6 h-6 object-contain grayscale" />
                <span class="text-base font-black tracking-tight italic uppercase">miEscuelaApp</span>
             </div>
             <p class="text-xs">© 2026 miEscuelaApp. Todos los derechos reservados.</p>
          </div>

        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { IonPage, IonContent, IonIcon } from '@ionic/vue';
import { shieldCheckmarkOutline, arrowBackOutline, school } from 'ionicons/icons';
</script>

<style scoped>
section p {
  @apply text-[15px] font-medium;
}
</style>
