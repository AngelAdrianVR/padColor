<template>
    
    <main class="mx-auto p-1 text-sm" :class="printing ? 'w-full' : 'w-1/2'">
        <Head :title="'Hoja Viajera ' + production.folio" />
        <PrintButton class="mx-auto" v-if="!printing" @click="print" />
        <!-- Header  -->
        <section class="flex justify-between items-center space-x-3 mb-1">
            <figure class="">
                <img class="w-40" src="@/../../public/images/padColor_logo.png" alt="logo">
            </figure>

            <div class="text-center">
                <h2 class="text-lg font-bold">Departamento Emisor</h2>
                <h3>Ingeniería de procesos</h3>
            </div>

            <div class="border border-[#D9D9D9] rounded-xl py-2 *:px-4">
                <p class="text-center font-bold">Código</p>
                <p class="py-2 text-center text-sm">{{ production.folio }}</p>
                <!-- <p class="border-t border-[#D9D9D9] py-2 text-center">1 de 1 ?</p> -->
            </div>
        </section>

        <!-- Body -->
        <body>
            <section class="border border-[#D9D9D9] rounded-xl py-2 px-7 flex justify-between space-x-7 mb-3">
                <div class="text-center">
                    <p class="text-[#676777]">Fecha de emisión</p>
                    <p class="text-black">{{ formatDate(production.created_at) }}</p>
                </div>

                <div class="text-center">
                    <p class="text-[#676777]">Próxima revisión</p>
                    <p class="text-black">{{ formatDate(addOneYear(production.created_at)) }}</p>
                </div>

                <div class="text-center">
                    <p class="text-[#676777]">Nivel de revisión</p>
                    <p class="text-black"></p>
                </div>
            </section>

            <!-- Orden de producción -->
            <section class="border border-[#D9D9D9] rounded-xl mb-3">
                <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Orden de producción</h2>
                <div class="border-t border-[#D9D9D9] py-2 px-7 flex justify-between space-x-7">
                    <div class="text-center">
                        <p class="text-[#676777]">No. Orden:</p>
                        <p class="text-black">{{ production.id }}</p>
                    </div>
                    <div>
                        <p class="text-[#676777]">Modelo:</p>
                        <p class="text-black">{{ production.product.name }}</p>
                    </div>
                    <div>
                        <p class="text-[#676777]">Fecha:</p>
                        <p class="text-black"></p>
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
                            <p class="text-black">{{ production.ts }}</p>
                        </div>
                        <!-- <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Dimensiones:</p>
                            <p class="text-black">{{ production.width }} x {{ production.large }}</p>
                        </div> -->
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">No. De Cambios:</p>
                            <p class="text-black">{{ production.changes }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Tamaños a Imprimir:</p>
                            <p class="text-black">{{ production.width }} x {{ production.large }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="text-black">{{ production.notes }}</p>
                        </div>
                    </section>

                    <!-- Parte derecha -->
                    <section>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Material/Calibre:</p>
                            <p class="text-black">{{ production.material }} / {{ production.gauge }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Acabado/Caras:</p>
                            <p class="text-black">{{ production.look + ' /' + production.faces + ' cara(s)' }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Impresión por cambio:</p>
                            <p class="text-black">{{ production.ts / production.changes }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <p class="text-[#676777]">Cantidad solicitada:</p>
                            <p class="text-black">{{ production.ts }} </p>
                        </div>
                    </section>
                </article>
            </section>

            <section class="grid grid-cols-2 gap-x-2">
                <!-- Surtido de material lado izquierdo -->
                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Surtido de Material</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Fecha de Surtido:</p>
                            <p class="w-[182px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Hojas Surtidas:</p>
                            <p class="w-[192px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Tarimas:</p>
                            <p class="w-[252px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-12 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <!-- Corte parte derecha -->
                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Corte</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[179px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[60px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[60px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[70px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[70px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-10 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Impresión</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[168px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[62px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[62px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[75px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[75px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Pliegos procesados:</p>
                            <p class="w-[169px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-12 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Suaje</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[168px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[62px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[62px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[75px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[75px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Pliegos procesados:</p>
                            <p class="w-[169px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-12 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <!-- salto de pagina para evitar cortes de la tabla en la impresión -->
                <div class="salto-pagina col-span-full"></div>

                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Barniz</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[175px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[62px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[62px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[77px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[77px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Pliegos procesados:</p>
                            <p class="w-[169px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-12 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Estampado</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[175px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[63px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[63px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[77px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[77px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Pliegos procesados:</p>
                            <p class="w-[169px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-12 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Realzado</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[175px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[63px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[63px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[80px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[80px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Pliegos procesados:</p>
                            <p class="w-[169px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-12 border border-[#D9D9D9]"></p>
                    </div>
                </article>

                <article class="border border-[#D9D9D9] rounded-xl mb-3">
                    <h2 class="font-bold py-1 bg-[#E8E8E8] rounded-t-xl text-center">Bolseadora</h2>
                    <div class="border-t border-[#D9D9D9] py-2 px-7 space-y-1">
                        <div class="flex items-center">
                            <p class="text-[#676777]">Operador:</p>
                            <p class="w-60 h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Máquina:</p>
                            <p class="w-[245px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Formato de corte:</p>
                            <p class="w-[175px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha inicio:</p>
                                <p class="w-[63px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora inicio:</p>
                                <p class="w-[63px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <article class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <p class="text-[#676777]">Fecha fin:</p>
                                <p class="w-[80px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                            <div class="flex items-center">
                                <p class="text-[#676777]">Hora fin:</p>
                                <p class="w-[80px] h-[20px] border border-[#D9D9D9]"></p>
                            </div>
                        </article>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Pliegos procesados:</p>
                            <p class="w-[169px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-[#676777]">Observaciones:</p>
                            <p class="w-[198px] h-[20px] border border-[#D9D9D9]"></p>
                        </div>
                        <p class="w-[305px] h-12 border border-[#D9D9D9]"></p>
                    </div>
                </article>
            </section>
        </body>
    </main>
</template>

<script>
import PrintButton from '@/Components/MyComponents/PrintButton.vue';
import { Head } from '@inertiajs/vue3';

export default {
data() {
    return {
        currentDate: new Date(), //fecha del día de hoy
        printing: false
    }
},
components:{
    Head,
    PrintButton
},
props:{
    production: Object
},
methods:{
    formatDate(date) {
        const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = months[d.getMonth()];
        const year = d.getFullYear();

        return `${day}/${month}/${year}`;
    },
    addOneYear(dateStr) {
        const date = new Date(dateStr);
        date.setFullYear(date.getFullYear() + 1);
        return date.toLocaleDateString(); // puedes personalizar el formato
    },
    print() {
      this.printing = true;
      setTimeout(() => {
        window.print();
      }, 200);
    },
    handleAfterPrint() {
      this.printing = false;
    },
},
mounted() {
    window.addEventListener('afterprint', this.handleAfterPrint);
},
beforeDestroy() {
    window.removeEventListener('afterprint', this.handleAfterPrint);
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
