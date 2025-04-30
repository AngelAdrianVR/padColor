<template>
    <main class="w-full mx-auto p-1 text-sm">
        <Head :title="'Hoja Viajera'" />
        <!-- Header  -->
        <section class="flex justify-between items-center space-x-3 mb-3">
            <figure class="">
                <img class="w-40" src="@/../../public/images/padColor_logo.png" alt="logo">
            </figure>

            <div class="text-center">
                <h2 class="text-lg font-bold">Departamento Emisor</h2>
                <h3>Ingeniería de procesos</h3>
            </div>

            <div class="border border-[#D9D9D9] rounded-xl py-2 *:px-3">
                <p class="text-center font-bold">Código</p>
                <p class="py-2 text-sm">{{ 'PADI-FT-PRO-07' }}</p>
                <p class="border-t border-[#D9D9D9] py-2 text-center">1 de 1</p>
            </div>
        </section>

        <!-- Body -->
        <body>
            <section class="border border-[#D9D9D9] rounded-xl py-2 px-7 flex justify-between space-x-7 mb-3">
                <div class="text-center">
                    <p class="text-[#676777]">Fecha de emisión</p>
                    <p class="text-black">{{ '07/08/2018' }}</p>
                </div>

                <div class="text-center">
                    <p class="text-[#676777]">Próxima revisión</p>
                    <p class="text-black">{{ '07/08/2019' }}</p>
                </div>

                <div class="text-center">
                    <p class="text-[#676777]">Nivel de revisión</p>
                    <p class="text-black">{{ '0' }}</p>
                </div>
            </section>

            <!-- Orden de producción -->
            <section class="border border-[#D9D9D9] rounded-xl mb-3">
                <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Orden de producción</h2>
                <div class="border-t border-[#D9D9D9] py-2 px-7 flex justify-between space-x-7">
                    <div class="text-center">
                        <p class="text-[#676777]">No. Orden:</p>
                        <p class="text-black">{{ '26405' }}</p>
                    </div>
                    <div>
                        <p class="text-[#676777]">Modelo:</p>
                        <p class="text-black">{{ '682158020347 Bolsa Grande Campeones-Ninguno' }}</p>
                    </div>
                    <div>
                        <p class="text-[#676777]">Fecha:</p>
                        <p class="text-black">{{ '14/03/2025' }}</p>
                    </div>
                </div>
            </section>

            <!-- Supervisión -->
            <section class="border border-[#D9D9D9] rounded-xl mb-3">
                <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Supervisión</h2>
                <article class="border-t border-[#D9D9D9] py-2 px-7 grid grid-cols-2">
                    <!-- Parte izquierda -->
                    <section>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Hojas necesarias:</p>
                            <p class="text-black">{{ '13,000' }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Dimensiones:</p>
                            <p class="text-black">{{ '73 x 51' }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">No. De Cambios:</p>
                            <p class="text-black">{{ '4' }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Tamaños a Imprimir:</p>
                            <p class="text-black">{{ '12,000' }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="text-black">{{ 'Mandar a máquina' }}</p>
                        </div>
                    </section>

                    <!-- Parte derecha -->
                    <section>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Material/Calibre:</p>
                            <p class="text-black">{{ 'Multicapa 13.4 pts' }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Acabado/Caras:</p>
                            <p class="text-black">{{ 'Brillante 0 Capas' }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Impresión por cambio:</p>
                            <p class="text-black">{{ '3,250' }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Cantidad solicitada:</p>
                            <p class="text-black">{{ '12,000' }}</p>
                        </div>
                    </section>
                </article>
            </section>

            <section class="grid grid-cols-2 gap-2">
                <!-- Surtido de material lado izquierdo -->
                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Surtido de Material</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Fecha de Surtido:</p>
                            <p class="w-[184px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Hojas Surtidas:</p>
                            <p class="w-[200px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Tarimas:</p>
                            <p class="w-[252px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-20 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <!-- Corte parte derecha -->
                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Corte</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[189px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-16 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <!-- salto de pagina para evitar cortes de la tabla en la impresión -->
                <div class="salto-pagina col-span-full"></div>

                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Impresión</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[189px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Pliegos procesados:</p>
                            <p class="w-[169px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-16 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Suaje</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[189px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Pliegos procesados:</p>
                            <p class="w-[169px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-16 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Barniz</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[189px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Pliegos procesados:</p>
                            <p class="w-[169px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-16 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Estampado</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[189px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Pliegos procesados:</p>
                            <p class="w-[169px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-16 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <!-- salto de pagina para evitar cortes de la tabla en la impresión -->
                <div class="salto-pagina col-span-full"></div>

                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Realzado</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[189px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Pliegos procesados:</p>
                            <p class="w-[169px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-16 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Bolseadora</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[189px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[100px] h-7 border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Pliegos procesados:</p>
                            <p class="w-[169px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-7 border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-16 border border-[#D9D9D9]"></p>
                    </div>
                </article>
            </section>
        </body>

    </main>
</template>

<script>
import { Head } from '@inertiajs/vue3';

export default {
data() {
    return {

    }
},
components:{
    Head,
}
}
</script>

<style>
@media print {
  .salto-pagina {
    page-break-before: always;
  }

  /* Evita que se corte una tabla dentro */
  table {
    page-break-inside: avoid;
  }

  section {
    page-break-inside: avoid;
  }
}
</style>
