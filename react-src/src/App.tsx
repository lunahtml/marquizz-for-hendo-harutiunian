//react-src/src/App.tsx
import React from 'react';
import SurveyEditor from './components/SurveyBuilder/SurveyEditor';
import QuestionsLibraryPage from './pages/QuestionsLibraryPage';
import SurveysList from './pages/SurveysList';
import AnalyticsDashboard from './pages/AnalyticsDashboard';

const App: React.FC = () => {
    const currentPath = window.location.pathname + window.location.search;
    const isEditor = document.getElementById('survey-sphere-editor');
    const isQuestionsRoot = document.getElementById('survey-sphere-questions-root');
    const isAnalytics = document.getElementById('survey-sphere-analytics-root');

    console.log('App rendering:', { isEditor: !!isEditor, isQuestionsRoot: !!isQuestionsRoot, isAnalytics: !!isAnalytics });

    if (isEditor) return <SurveyEditor />;
    if (isQuestionsRoot) return <QuestionsLibraryPage />;
    if (isAnalytics) return <AnalyticsDashboard />;

    return <SurveysList />;
};

export default App;