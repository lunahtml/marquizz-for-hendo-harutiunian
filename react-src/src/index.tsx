import React from 'react';
import { createRoot } from '@wordpress/element';
import App from './App';
import './styles/global.css';

console.log('=== index.tsx loaded ===');

// Проверяем, что мы в админке (есть контейнеры админки)
const adminContainers = [
    'survey-sphere-editor',
    'survey-sphere-questions-root',
    'survey-sphere-surveys-root',
    'survey-sphere-analytics-root'  // ← ДОБАВИТЬ
];

let container = null;
for (const id of adminContainers) {
    container = document.getElementById(id);
    console.log('Checking container:', id, container);
    if (container) break;
}

console.log('Selected container:', container);

// Если нашли админский контейнер — рендерим App
if (container) {
    const root = createRoot(container);
    root.render(<App />);
} else {
    console.error('No admin container found!');
}