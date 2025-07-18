import './bootstrap';
import Alpine from 'alpinejs';

// Importar CSS via JavaScript (necessário para vite_js_only)
import '../css/app.css';

// Inicializar Alpine.js
window.Alpine = Alpine;
Alpine.start();

// jQuery global (se necessário para AdminLTE)
import $ from 'jquery';
window.$ = window.jQuery = $;

console.log('App.js carregado');