//react-src/src/components/SurveyFrontend/ChartView.tsx
import React from 'react';
import {
    Chart as ChartJS,
    ArcElement,
    RadialLinearScale,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    PolarAreaController,
    BarController,
    RadarController,
    DoughnutController,
    Filler
} from 'chart.js';
import { PolarArea, Bar, Radar, Doughnut } from 'react-chartjs-2';
import type { Segment } from '../../types';

ChartJS.register(
    ArcElement, RadialLinearScale, CategoryScale, LinearScale,
    BarElement, Title, Tooltip, Legend, Filler,
    PolarAreaController, BarController, RadarController, DoughnutController
);

interface Props {
    chartType: string;
    segments: Segment[];
    percentages: Record<string, number>;
}

const ChartView: React.FC<Props> = ({ chartType, segments, percentages }) => {
    if (segments.length === 0) {
        return <p>Нет сегментов для отображения</p>;
    }

    const labels = segments.map(s => s.name);
    const data = segments.map(s => percentages[s.id] || 0);
    const colors = segments.map(s => s.color + 'CC');
    const borderColors = segments.map(s => s.color);

    if (chartType === 'polarArea') {
        const chartData = {
            labels,
            datasets: [
                // Фоновые зоны
                { data: segments.map(() => 25), backgroundColor: 'rgba(220, 53, 69, 0.15)', borderWidth: 0 },
                { data: segments.map(() => 50), backgroundColor: 'rgba(253, 126, 20, 0.12)', borderWidth: 0 },
                { data: segments.map(() => 75), backgroundColor: 'rgba(255, 193, 7, 0.1)', borderWidth: 0 },
                { data: segments.map(() => 100), backgroundColor: 'rgba(40, 167, 69, 0.05)', borderWidth: 0 },
                // Данные пользователя
                {
                    data,
                    backgroundColor: colors,
                    borderColor: borderColors,
                    borderWidth: 2
                }
            ]
        };

        return <PolarArea data={chartData} options={{
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' as const, labels: { font: { size: 12 } } },
                title: { display: true, text: 'Результаты по сегментам', font: { size: 16 } }
            },
            scales: {
                r: {
                    max: 100,
                    ticks: { stepSize: 25, callback: (v) => v + '%' }
                }
            }
        }} />;
    }

    const chartData = {
        labels,
        datasets: [{
            label: 'Score (%)',
            data,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 1
        }]
    };

    const options = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { legend: { display: chartType !== 'bar' } },
        scales: chartType === 'bar' || chartType === 'radar' ? { y: { max: 100 } } : {}
    };

    switch (chartType) {
        case 'bar':
            return <Bar data={chartData} options={options} />;
        case 'radar':
            return <Radar data={chartData} options={options} />;
        case 'doughnut':
            return <Doughnut data={chartData} options={options} />;
        default:
            return <Bar data={chartData} options={options} />;
    }
};

export default ChartView;