        </div>
    </div>
    
    <!-- Botón Flotante del Chat IA -->
    <button onclick="toggleChat()" 
            class="fixed bottom-6 right-6 w-14 h-14 bg-noir text-white rounded-full shadow-lg hover:shadow-xl hover:scale-110 transition-all duration-200 flex items-center justify-center z-40 group">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
        </svg>
        <span class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
    </button>

    <!-- Modal Chat IA -->
    <div id="modalChatIA" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-start sm:items-center justify-center z-50 p-0 sm:p-4">
        <!-- Ventana desde arriba en mobile, modal centrado en desktop -->
        <div class="bg-white dark:bg-gray-800 w-full max-h-[85vh] sm:h-auto rounded-b-2xl sm:rounded-2xl sm:max-w-lg sm:max-h-[600px] flex flex-col shadow-2xl mt-0 sm:mt-0">
            <!-- Header -->
            <div class="flex items-center justify-between p-3 sm:p-4 border-b border-gray-200 dark:border-gray-700 bg-noir text-white rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold">Asistente IA</h3>
                        <p class="text-xs opacity-80">Hotel Cecil</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button id="btnToggleVoz" onclick="toggleVozRespuestas()" class="p-2 hover:bg-white hover:bg-opacity-10 rounded-lg transition" title="Activar/Desactivar respuestas de voz">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                        </svg>
                    </button>
                    <button onclick="limpiarChat()" class="p-2 hover:bg-white hover:bg-opacity-10 rounded-lg transition" title="Limpiar chat">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    <button onclick="cerrarChat()" class="p-2 hover:bg-white hover:bg-opacity-10 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Contenido del Chat -->
            <div id="chatContenido" class="flex-1 overflow-y-auto p-4 space-y-3" style="background-color: #f0f2f5; max-height: 60vh; min-height: 300px;">
                <div class="flex justify-center mb-4">
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-4 py-3 text-center text-gray-600 dark:text-gray-300 text-sm max-w-sm">
                        <div class="font-semibold">¡Hola! Soy el asistente del Hotel Cecil.</div>
                        <div class="text-xs mt-1 text-gray-500 dark:text-gray-400">Pregúntame sobre huéspedes, finanzas, habitaciones, etc.</div>
                    </div>
                </div>
            </div>
            
            <!-- Input -->
            <div class="p-3 sm:p-4 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <button 
                        id="btnVoz"
                        onclick="toggleVoz()" 
                        class="w-9 h-9 sm:w-11 sm:h-11 flex-shrink-0 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                        <svg id="iconMicrofono" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                        </svg>
                    </button>
                    <input 
                        type="text" 
                        id="inputMensaje" 
                        placeholder="Escribe tu pregunta..."
                        class="flex-1 px-3 py-2 sm:px-4 sm:py-3 rounded-full border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-noir focus:border-transparent dark:bg-gray-800 dark:text-white text-sm"
                    />
                    <button 
                        onclick="enviarMensaje()" 
                        class="w-9 h-9 sm:w-11 sm:h-11 flex-shrink-0 flex items-center justify-center rounded-full bg-noir text-white hover:bg-opacity-90 transition-all">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </div>
                <div class="text-xs text-gray-400 mt-2 text-center hidden sm:block">
                    <span id="statusVoz">Presiona Enter para enviar o usa el micrófono</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer Minimalista -->
    <footer class="border-t border-gray-100 mt-20">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="text-sm text-gray-500">
                    &copy; <?php echo date('Y'); ?> Hotel Cecil. Sistema de Gestión Hotelera.
                </div>
                <div class="flex items-center space-x-6 mt-4 md:mt-0">
                    <span class="text-xs text-gray-400">v1.0.0</span>
                    <div class="h-1 w-1 bg-gray-300 rounded-full"></div>
                    <span class="text-xs text-gray-400">Powered by Modern Stack</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Script del Chat IA -->
    <script>
        // Pasar BASE_PATH a JavaScript
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
    </script>
    <script src="<?php echo BASE_PATH; ?>/assets/js/chat_ia.js"></script>
</body>
</html>