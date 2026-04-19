import React from 'react';
import { createRoot } from '@wordpress/element';
import SurveyPage from '../pages/SurveyPage';
import '../styles/global.css';

const container = document.getElementById('survey-sphere-root');
if (container) {
    const root = createRoot(container);
    root.render(<SurveyPage />);
}