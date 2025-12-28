// Chat IA del Hotel Cecil
let chatAbierto = false;
let reconocimientoVoz = null;
let escuchando = false;
let vozActivada = false; // La IA NO hablará por defecto

// Inicializar reconocimiento de voz
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    reconocimientoVoz = new SpeechRecognition();
    reconocimientoVoz.continuous = false;
    reconocimientoVoz.lang = 'es-ES';
    reconocimientoVoz.interimResults = false;
    reconocimientoVoz.maxAlternatives = 1;

    reconocimientoVoz.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        document.getElementById('inputMensaje').value = transcript;
        detenerVoz();
    };

    reconocimientoVoz.onerror = (event) => {
        console.error('Error de reconocimiento de voz:', event.error);
        detenerVoz();
        if (event.error === 'no-speech') {
            document.getElementById('statusVoz').textContent = 'No se detectó voz. Intenta de nuevo.';
        }
    };

    reconocimientoVoz.onend = () => {
        detenerVoz();
    };
}

function toggleVoz() {
    if (!reconocimientoVoz) {
        alert('Tu navegador no soporta reconocimiento de voz. Usa Chrome o Edge.');
        return;
    }

    if (escuchando) {
        detenerVoz();
    } else {
        iniciarVoz();
    }
}

function iniciarVoz() {
    escuchando = true;
    reconocimientoVoz.start();
    
    // Cambiar UI
    const btn = document.getElementById('btnVoz');
    const icon = document.getElementById('iconMicrofono');
    btn.classList.add('bg-red-500');
    btn.classList.remove('bg-gray-100', 'dark:bg-gray-800');
    icon.classList.add('text-white', 'animate-pulse');
    icon.classList.remove('text-gray-700', 'dark:text-gray-300');
    
    document.getElementById('statusVoz').textContent = '🎤 Escuchando... Habla ahora';
}

function detenerVoz() {
    if (reconocimientoVoz && escuchando) {
        reconocimientoVoz.stop();
    }
    escuchando = false;
    
    // Restaurar UI
    const btn = document.getElementById('btnVoz');
    const icon = document.getElementById('iconMicrofono');
    btn.classList.remove('bg-red-500');
    btn.classList.add('bg-gray-100', 'dark:bg-gray-800');
    icon.classList.remove('text-white', 'animate-pulse');
    icon.classList.add('text-gray-700', 'dark:text-gray-300');
    
    document.getElementById('statusVoz').textContent = 'Presiona Enter para enviar o usa el micrófono';
}

function leerRespuesta(texto) {
    if ('speechSynthesis' in window) {
        // Cancelar cualquier lectura anterior
        window.speechSynthesis.cancel();
        
        // Limpiar markdown y símbolos antes de leer
        let textoLimpio = texto
            .replace(/\*\*/g, '')  // Eliminar negritas
            .replace(/\*/g, '')     // Eliminar asteriscos
            .replace(/__/g, '')     // Eliminar subrayado doble
            .replace(/_/g, '')      // Eliminar subrayado simple
            .replace(/~~(.*?)~~/g, '$1')  // Eliminar tachado
            .replace(/`/g, '')      // Eliminar código inline
            .replace(/#{1,6}\s/g, '')  // Eliminar headers markdown
            .replace(/\[([^\]]+)\]\([^\)]+\)/g, '$1')  // Convertir links a texto
            .replace(/[-•]\s/g, '');  // Eliminar viñetas
        
        const utterance = new SpeechSynthesisUtterance(textoLimpio);
        utterance.lang = 'es-ES';
        utterance.rate = 1.1; // Un poco más rápido
        utterance.pitch = 1.0;
        utterance.volume = 1.0;
        
        window.speechSynthesis.speak(utterance);
    }
}

function toggleVozRespuestas() {
    vozActivada = !vozActivada;
    const btn = document.getElementById('btnToggleVoz');
    const status = document.getElementById('statusVoz');
    
    if (vozActivada) {
        btn.classList.add('bg-green-100', 'dark:bg-green-900');
        if (status) status.textContent = '🔊 Respuestas con voz activadas';
    } else {
        btn.classList.remove('bg-green-100', 'dark:bg-green-900');
        if (status) status.textContent = '🔇 Respuestas con voz desactivadas';
        window.speechSynthesis.cancel(); // Detener cualquier voz actual
    }
    
    setTimeout(() => {
        if (status) status.textContent = 'Presiona Enter para enviar o usa el micrófono';
    }, 2000);
}

function toggleChat() {
    const modal = document.getElementById('modalChatIA');
    chatAbierto = !chatAbierto;
    
    if (chatAbierto) {
        modal.classList.remove('hidden');
        document.getElementById('inputMensaje').focus();
    } else {
        modal.classList.add('hidden');
    }
}

function cerrarChat() {
    chatAbierto = false;
    document.getElementById('modalChatIA').classList.add('hidden');
}

async function enviarMensaje() {
    const input = document.getElementById('inputMensaje');
    const mensaje = input.value.trim();
    
    if (!mensaje) return;
    
    // Mostrar mensaje del usuario
    agregarMensaje(mensaje, 'usuario');
    input.value = '';
    
    // Mostrar indicador de "escribiendo..."
    const loadingId = agregarMensaje('Escribiendo...', 'ia', true);
    
    try {
        const response = await fetch(BASE_PATH + '/controllers/chat_ia.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ mensaje: mensaje })
        });
        
        const data = await response.json();
        
        // Remover indicador de carga
        const loadingEl = document.getElementById(loadingId);
        if (loadingEl) loadingEl.remove();
        
        if (data.error) {
            agregarMensaje('Error: ' + data.error, 'ia');
        } else {
            agregarMensaje(data.respuesta, 'ia');
            // Leer respuesta en voz alta si está activada
            if (vozActivada) {
                leerRespuesta(data.respuesta);
            }
        }
        
    } catch (error) {
        const loadingEl = document.getElementById(loadingId);
        if (loadingEl) loadingEl.remove();
        agregarMensaje('Error al conectar con el asistente: ' + error.message, 'ia');
    }
}

function agregarMensaje(texto, tipo, isLoading = false) {
    const contenedor = document.getElementById('chatContenido');
    const mensajeId = 'msg-' + Date.now();
    
    const esUsuario = tipo === 'usuario';
    const bgColor = esUsuario 
        ? 'bg-noir text-white rounded-tl-2xl rounded-tr-sm rounded-bl-2xl rounded-br-2xl' 
        : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-tl-sm rounded-tr-2xl rounded-bl-2xl rounded-br-2xl shadow-sm';
    const align = esUsuario ? 'justify-end' : 'justify-start';
    
    const mensajeDiv = document.createElement('div');
    mensajeDiv.id = mensajeId;
    mensajeDiv.className = `flex ${align} mb-2`;
    
    const textoFormateado = texto.replace(/\n/g, '<br>');
    
    mensajeDiv.innerHTML = `
        <div class="max-w-[80%] px-3 py-2 ${bgColor} ${isLoading ? 'animate-pulse' : ''}">
            <div class="text-sm whitespace-pre-wrap">${textoFormateado}</div>
            <div class="text-xs ${esUsuario ? 'text-gray-200' : 'text-gray-500 dark:text-gray-400'} mt-1">${new Date().toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' })}</div>
        </div>
    `;
    
    contenedor.appendChild(mensajeDiv);
    contenedor.scrollTop = contenedor.scrollHeight;
    
    return mensajeId;
}

function limpiarChat() {
    const contenedor = document.getElementById('chatContenido');
    contenedor.innerHTML = `
        <div class="flex justify-center mb-4">
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-4 py-3 text-center text-gray-600 dark:text-gray-300 text-sm max-w-sm">
                <div class="font-semibold">¡Hola! Soy el asistente del Hotel Cecil.</div>
                <div class="text-xs mt-1 text-gray-500 dark:text-gray-400">Pregúntame sobre huéspedes, finanzas, habitaciones, etc.</div>
            </div>
        </div>
    `;
}

// Enter para enviar
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('inputMensaje');
    if (input) {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                enviarMensaje();
            }
        });
    }
});
