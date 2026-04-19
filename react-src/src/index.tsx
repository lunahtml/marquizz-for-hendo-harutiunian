import React from 'react';
import { createRoot } from '@wordpress/element';
import App from './App';
import './styles/global.css';

// Проверяем, что мы в админке (есть контейнеры админки)
const adminContainers = [
    'survey-sphere-editor',
    'survey-sphere-questions-root',
    'survey-sphere-surveys-root'
];

let container = null;
for (const id of adminContainers) {
    container = document.getElementById(id);
    if (container) break;
}

// Если нашли админский контейнер — рендерим App
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}