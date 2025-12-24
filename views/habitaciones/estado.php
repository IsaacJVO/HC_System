<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Habitacion.php';

$page_title = 'Gestión de Estados';

$habitacionModel = new Habitacion();

// Procesar cambios de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['habitacion_id'])) {
    $habitacion_id = clean_input($_POST['habitacion_id']);
    $nuevo_estado = clean_input($_POST['nuevo_estado']);
    
    $conn = getConnection();
    $sql = "UPDATE habitaciones SET estado = :estado WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([':estado' => $nuevo_estado, ':id' => $habitacion_id]);
    
    if ($result) {
        $mensaje = "Estado actualizado correctamente";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al actualizar el estado";
        $tipo_mensaje = "error";
    }
}

$habitaciones = $habitacionModel->obtenerTodas();

// Agrupar por piso
$por_piso = [
    '3' => [],
    '2' => [],
    '1' => []
];

foreach ($habitaciones as $hab) {
    $primer_digito = substr($hab['numero'], 0, 1);
    if (isset($por_piso[$primer_digito])) {
        $por_piso[$primer_digito][] = $hab;
    }
}

// Ordenar habitaciones por número dentro de cada piso
foreach ($por_piso as $piso => $habs) {
    usort($por_piso[$piso], function($a, $b) {
        return (int)$a['numero'] - (int)$b['numero'];
    });
}

// Contar estados
$total_disponibles = count(array_filter($habitaciones, fn($h) => $h['estado'] === 'disponible'));
$total_ocupadas = count(array_filter($habitaciones, fn($h) => $h['estado'] === 'ocupado'));
$total_limpieza = count(array_filter($habitaciones, fn($h) => $h['estado'] === 'limpieza'));
$total_mantenimiento = count(array_filter($habitaciones, fn($h) => $h['estado'] === 'mantenimiento'));

include __DIR__ . '/../../includes/header.php';
?>

<style>
body {
    background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
}

.dark body {
    background: linear-gradient(135deg, #0a0a0a 0%, #171717 100%);
}

.glass-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
}

.dark .glass-card {
    background: rgba(23, 23, 23, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.room-cell {
    aspect-ratio: 1;
    border: 2px solid #e5e5e5;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    position: relative;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.room-cell:hover {
    border-color: #000;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

/* Estados con todo el fondo de color para máxima visibilidad */
.room-cell.disponible { 
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border-color: #10b981;
}
.room-cell.ocupado { 
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-color: #ef4444;
}
.room-cell.limpieza { 
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-color: #f59e0b;
}
.room-cell.mantenimiento { 
    background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
    border-color: #6b7280;
}

/* Dark mode para habitaciones */
.dark .room-cell {
    border: 2px solid #374151;
}

.dark .room-cell:hover {
    border-color: #fff;
    box-shadow: 0 4px 16px rgba(255, 255, 255, 0.12);
}

.dark .room-cell.disponible { 
    background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
    border-color: #10b981;
}
.dark .room-cell.ocupado { 
    background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%);
    border-color: #ef4444;
}
.dark .room-cell.limpieza { 
    background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
    border-color: #f59e0b;
}
.dark .room-cell.mantenimiento { 
    background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
    border-color: #9ca3af;
}

.floor-label {
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #9ca3af;
    font-weight: 500;
}

.status-dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.9);
}

.status-dot.disponible { 
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 0 0 3px #d1fae5;
}
.status-dot.ocupado { 
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    box-shadow: 0 0 0 3px #fee2e2;
}
.status-dot.limpieza { 
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 0 0 3px #fef3c7;
}
.status-dot.mantenimiento { 
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    box-shadow: 0 0 0 3px #e5e7eb;
}

.modal-overlay {
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.modal-content {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(40px);
    -webkit-backdrop-filter: blur(40px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.btn-state {
    border: 1px solid rgba(0, 0, 0, 0.06);
    background: rgba(255, 255, 255, 0.9);
    color: #171717;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    backdrop-filter: blur(10px);
}

.btn-state:hover {
    border-color: rgba(0, 0, 0, 0.2);
    background: rgba(255, 255, 255, 1);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.btn-state:active {
    transform: translateY(0);
}

.btn-primary {
    background: linear-gradient(135deg, #171717 0%, #404040 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.btn-primary:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
    transform: translateY(-2px);
}

.btn-primary:active {
    transform: translateY(0);
}

.legend-item {
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    font-size: 15px;
    font-weight: 500;
}

.dark .legend-item {
    background: rgba(23, 23, 23, 0.9);
    border: 2px solid rgba(255, 255, 255, 0.1);
}

.legend-item:hover {
    background: rgba(255, 255, 255, 1);
    transform: translateY(-1px);
    border-color: rgba(0, 0, 0, 0.15);
}

.dark .legend-item:hover {
    background: rgba(30, 30, 30, 1);
    border-color: rgba(255, 255, 255, 0.2);
}
</style>

<div class="max-w-7xl mx-auto px-4 py-8">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-12">
        <div>
            <h1 class="text-3xl font-light tracking-tight text-gray-900 dark:text-white mb-1">Estado de Habitaciones</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Hotel Cecil</p>
        </div>
        <a href="<?php echo BASE_PATH; ?>/index.php" class="btn-primary inline-block" style="text-decoration: none;">
            ← Inicio
        </a>
    </div>

    <?php if (isset($mensaje)): ?>
    <div class="mb-8 glass-card px-6 py-4">
        <p class="text-sm <?php echo $tipo_mensaje === 'success' ? 'text-green-900 dark:text-green-300' : 'text-red-900 dark:text-red-300'; ?>"><?php echo $mensaje; ?></p>
    </div>
    <?php endif; ?>

    <!-- Leyenda -->
    <div class="flex items-center gap-4 mb-12 text-sm flex-wrap">
        <div class="legend-item flex items-center gap-3 rounded-xl">
            <span class="status-dot disponible"></span>
            <span class="text-gray-900 dark:text-white font-semibold">Disponible</span>
            <span class="text-gray-500 dark:text-gray-400 font-medium ml-1">(<?php echo $total_disponibles; ?>)</span>
        </div>
        <div class="legend-item flex items-center gap-3 rounded-xl">
            <span class="status-dot ocupado"></span>
            <span class="text-gray-900 dark:text-white font-semibold">Ocupada</span>
            <span class="text-gray-500 dark:text-gray-400 font-medium ml-1">(<?php echo $total_ocupadas; ?>)</span>
        </div>
        <div class="legend-item flex items-center gap-3 rounded-xl">
            <span class="status-dot limpieza"></span>
            <span class="text-gray-900 dark:text-white font-semibold">Limpieza</span>
            <span class="text-gray-500 dark:text-gray-400 font-medium ml-1">(<?php echo $total_limpieza; ?>)</span>
        </div>
        <div class="legend-item flex items-center gap-3 rounded-xl">
            <span class="status-dot mantenimiento"></span>
            <span class="text-gray-900 dark:text-white font-semibold">Mantenimiento</span>
            <span class="text-gray-500 dark:text-gray-400 font-medium ml-1">(<?php echo $total_mantenimiento; ?>)</span>
        </div>
    </div>

    <!-- Pisos -->
    <?php foreach (['3', '2', '1'] as $num_piso): 
        if (empty($por_piso[$num_piso])) continue;
    ?>
    <div class="mb-12">
        <div class="flex items-baseline gap-4 mb-4">
            <h2 class="floor-label">Piso <?php echo $num_piso; ?></h2>
            <div class="h-px bg-gray-200 flex-1"></div>
        </div>
        
        <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2">
            <?php foreach ($por_piso[$num_piso] as $hab): ?>
            <div class="room-cell <?php echo $hab['estado']; ?>" 
                 onclick="openModal(<?php echo htmlspecialchars(json_encode($hab)); ?>)">
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $hab['numero']; ?></span>
                    <span class="text-xs text-gray-600 dark:text-gray-300 mt-1 font-medium"><?php echo $hab['tipo']; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<!-- Modal -->
<div id="modal" class="modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-4" onclick="closeModal()">
    <div class="modal-content max-w-sm w-full p-8" onclick="event.stopPropagation()">
        <div class="mb-6">
            <div class="flex items-baseline gap-2 mb-1">
                <h3 class="text-2xl font-light text-gray-900" id="m-numero"></h3>
                <span class="text-sm text-gray-500" id="m-tipo"></span>
            </div>
            <p class="text-sm text-gray-500">Bs. <span id="m-precio"></span> por noche</p>
        </div>
        
        <div class="mb-6 pb-6 border-b border-gray-200">
            <p class="text-xs uppercase tracking-wider text-gray-400 mb-2">Estado actual</p>
            <div class="flex items-center gap-2">
                <span class="status-dot" id="m-status-dot"></span>
                <span class="text-sm text-gray-900" id="m-status-text"></span>
            </div>
        </div>
        
        <form method="POST" class="space-y-2">
            <input type="hidden" name="habitacion_id" id="m-id">
            
            <button type="submit" name="nuevo_estado" value="disponible" class="btn-state w-full text-left">
                Disponible
            </button>
            
            <button type="submit" name="nuevo_estado" value="limpieza" class="btn-state w-full text-left">
                Limpieza
            </button>
            
            <button type="submit" name="nuevo_estado" value="mantenimiento" class="btn-state w-full text-left">
                Mantenimiento
            </button>
            
            <div class="pt-4">
                <button type="button" onclick="closeModal()" class="text-sm text-gray-500 hover:text-gray-900 transition">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const statusMap = {
    'disponible': { text: 'Disponible', class: 'disponible' },
    'ocupado': { text: 'Ocupada', class: 'ocupado' },
    'limpieza': { text: 'Necesita limpieza', class: 'limpieza' },
    'mantenimiento': { text: 'En mantenimiento', class: 'mantenimiento' }
};

function openModal(room) {
    document.getElementById('m-numero').textContent = room.numero;
    document.getElementById('m-tipo').textContent = room.tipo;
    document.getElementById('m-precio').textContent = parseFloat(room.precio_dia).toFixed(2);
    document.getElementById('m-id').value = room.id;
    
    const status = statusMap[room.estado];
    document.getElementById('m-status-dot').className = 'status-dot ' + status.class;
    document.getElementById('m-status-text').textContent = status.text;
    
    document.getElementById('modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
