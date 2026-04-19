//react-src/src/services/api.ts
import apiFetch from '@wordpress/api-fetch';

// Устанавливаем корневой URL без wp-json
apiFetch.use(apiFetch.createRootURLMiddleware('/chess/wp-json'));

const API_BASE = '/survey-sphere/v1';

export const segmentsApi = {
    getBySurvey: (surveyId: string) =>
        apiFetch({ path: `${API_BASE}/segments?survey_id=${surveyId}` }),

    create: (data: { survey_id: string; name: string; color?: string; icon?: string }) =>
        apiFetch({
            path: `${API_BASE}/segments`,
            method: 'POST',
            data
        }),

    update: (id: string, data: any) =>
        apiFetch({
            path: `${API_BASE}/segments/${id}`,
            method: 'PUT',
            data
        }),

    delete: (id: string) =>
        apiFetch({
            path: `${API_BASE}/segments/${id}`,
            method: 'DELETE'
        }),
};