import React from 'react';
import { createRoot } from '@wordpress/element';
import App from './App';
import './styles/global.css';

const container = document.getElementById('survey-sphere-root');
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}