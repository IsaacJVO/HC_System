<!DOCTYPE html>
<html lang="es" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Hotel Cecil</title>
    <script>
        // Dark mode antes de cargar para evitar flash
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'noir': '#0a0a0a',
                        'slate': '#1a1a1a',
                        'pearl': '#fafafa',
                        'mist': '#f5f5f5',
                        'accent': '#c9a962',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .animate-slide-down { animation: slideDown 0.3s ease-out; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out; }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .glass-effect:not(.dark *) {
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .dark .glass-effect {
            background: rgba(23, 23, 23, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #c9a962;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background-color 0.3s ease;
        }
    </style>
</head>
<body class="min-h-screen bg-white dark:bg-noir transition-colors duration-300">
    
    <!-- Navigation Ultra Minimalista -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass-effect border-b border-gray-100 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-center justify-between h-20">
                <!-- Logo Minimalista -->
                <a href="<?php echo BASE_PATH; ?>/index.php" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 bg-noir dark:bg-white rounded-lg flex items-center justify-center group-hover:bg-accent transition-colors duration-300">
                        <span class="text-white dark:text-noir font-bold text-lg">HC</span>
                    </div>
                    <span class="text-xl font-semibold text-noir dark:text-white tracking-tight">Hotel Cecil</span>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-1">
                    <!-- Dark Mode Toggle -->
                    <button id="theme-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors mr-2" title="Toggle dark mode">
                        <svg id="theme-toggle-light-icon" class="w-5 h-5 text-gray-700 dark:text-gray-300 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path>
                        </svg>
                        <svg id="theme-toggle-dark-icon" class="w-5 h-5 text-gray-700 dark:text-gray-300 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>
                    
                    <a href="<?php echo BASE_PATH; ?>/index.php" class="nav-link px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-noir dark:hover:text-white">
                        Dashboard
                    </a>
                    
                    <!-- Dropdown Huéspedes -->
                    <div class="relative group">
                        <button class="nav-link px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-noir dark:hover:text-white flex items-center space-x-1">
                            <span>Huéspedes</span>
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-180 duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-2 w-64 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 animate-slide-down">
                            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                                <a href="<?php echo BASE_PATH; ?>/views/huespedes/nuevo.php" class="block px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-mist dark:hover:bg-gray-800 hover:text-noir dark:hover:text-white transition-colors duration-150">
                                    <div class="font-medium">Nuevo Registro</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Agregar nuevo huésped</div>
                                </a>
                                <div class="border-t border-gray-100 dark:border-gray-800"></div>
                                <a href="<?php echo BASE_PATH; ?>/views/huespedes/activos.php" class="block px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-mist dark:hover:bg-gray-800 hover:text-noir dark:hover:text-white transition-colors duration-150">
                                    <div class="font-medium">Huéspedes Activos</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ver estadías actuales</div>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dropdown Finanzas -->
                    <div class="relative group">
                        <button class="nav-link px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-noir dark:hover:text-white flex items-center space-x-1">
                            <span>Finanzas</span>
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-180 duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-2 w-64 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 animate-slide-down">
                            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                                <a href="<?php echo BASE_PATH; ?>/views/finanzas/ingresos.php" class="block px-5 py-3.5 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 transition-colors duration-150 group/item">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover/item:bg-green-500 transition-colors">
                                            <svg class="w-5 h-5 text-green-600 group-hover/item:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold">Ingresos Extras</div>
                                            <div class="text-xs text-gray-500">Ganancias adicionales</div>
                                        </div>
                                    </div>
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <a href="<?php echo BASE_PATH; ?>/views/finanzas/egresos.php" class="block px-5 py-3.5 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors duration-150 group/item">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center group-hover/item:bg-red-500 transition-colors">
                                            <svg class="w-5 h-5 text-red-600 group-hover/item:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold">Egresos</div>
                                            <div class="text-xs text-gray-500">Salidas de caja</div>
                                        </div>
                                    </div>
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <a href="<?php echo BASE_PATH; ?>/views/finanzas/resumen.php" class="block px-5 py-3.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-150 group/item">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover/item:bg-blue-500 transition-colors">
                                            <svg class="w-5 h-5 text-blue-600 group-hover/item:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold">Resumen</div>
                                            <div class="text-xs text-gray-500">Ver balance general</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dropdown Habitaciones -->
                    <div class="relative group">
                        <button class="nav-link px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-noir dark:hover:text-white flex items-center space-x-1">
                            <span>Habitaciones</span>
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-180 duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-2 w-64 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 animate-slide-down">
                            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                                <a href="<?php echo BASE_PATH; ?>/views/habitaciones/estado.php" class="block px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-mist dark:hover:bg-gray-800 hover:text-noir dark:hover:text-white transition-colors duration-150">
                                    <div class="font-medium">Estado de Habitaciones</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Disponibilidad en tiempo real</div>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <a href="<?php echo BASE_PATH; ?>/views/reportes/planilla.php" class="nav-link px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-noir dark:hover:text-white">
                        Reportes
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg hover:bg-mist dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-6 h-6 text-noir dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
            <div class="px-6 py-4 space-y-1">
                <a href="<?php echo BASE_PATH; ?>/index.php" class="block px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-mist dark:hover:bg-gray-800 rounded-lg transition-colors">Dashboard</a>
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Huéspedes</div>
                <a href="<?php echo BASE_PATH; ?>/views/huespedes/nuevo.php" class="block px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-mist dark:hover:bg-gray-800 rounded-lg transition-colors">Nuevo Registro</a>
                <a href="<?php echo BASE_PATH; ?>/views/huespedes/activos.php" class="block px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-mist dark:hover:bg-gray-800 rounded-lg transition-colors">Activos</a>
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Finanzas</div>
                <a href="<?php echo BASE_PATH; ?>/views/finanzas/ingresos.php" class="block px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-mist dark:hover:bg-gray-800 rounded-lg transition-colors">Ingresos</a>
                <a href="<?php echo BASE_PATH; ?>/views/finanzas/egresos.php" class="block px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-mist dark:hover:bg-gray-800 rounded-lg transition-colors">Egresos</a>
                <a href="<?php echo BASE_PATH; ?>/views/finanzas/pagos_qr.php" class="block px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-mist dark:hover:bg-gray-800 rounded-lg transition-colors">Pagos QR</a>
                <a href="<?php echo BASE_PATH; ?>/views/finanzas/resumen.php" class="block px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-mist dark:hover:bg-gray-800 rounded-lg transition-colors">Resumen</a>
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Habitaciones</div>
                <a href="<?php echo BASE_PATH; ?>/views/habitaciones/estado.php" class="block px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-mist dark:hover:bg-gray-800 rounded-lg transition-colors">Estado</a>
                <a href="<?php echo BASE_PATH; ?>/views/reportes/planilla.php" class="block px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-mist dark:hover:bg-gray-800 rounded-lg transition-colors">Reportes</a>
            </div>
        </div>
    </nav>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
        
        // Dark mode toggle
        const themeToggle = document.getElementById('theme-toggle');
        
        themeToggle.addEventListener('click', function() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        });
    </script>
    
    <!-- Main Content Container -->
    <div class="pt-20">
        <div class="max-w-7xl mx-auto px-6 py-12">
