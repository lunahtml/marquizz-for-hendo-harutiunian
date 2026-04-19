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
    Filler,
    TooltipItem
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

    const labels = segments.map((s: Segment) => s.name);
    const data = segments.map((s: Segment) => percentages[s.id] || 0);
    const colors = segments.map((s: Segment) => s.color + 'CC');
    const borderColors = segments.map((s: Segment) => s.color);

    if (chartType === 'polarArea') {
        const chartData = {
            labels,
            datasets: [
                {
                    label: 'Ваш результат',
                    data,
                    backgroundColor: colors,
                    borderColor: borderColors,
                    borderWidth: 2,
                },
                {
                    label: 'Критический (25%)',
                    data: segments.map(() => 25),
                    backgroundColor: 'rgba(220, 53, 69, 0.15)',
                    borderWidth: 0
                },
                {
                    label: 'Низкий (50%)',
                    data: segments.map(() => 50),
                    backgroundColor: 'rgba(253, 126, 20, 0.12)',
                    borderWidth: 0
                },
                {
                    label: 'Средний (75%)',
                    data: segments.map(() => 75),
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    borderWidth: 0
                },
                {
                    label: 'Высокий (100%)',
                    data: segments.map(() => 100),
                    backgroundColor: 'rgba(40, 167, 69, 0.05)',
                    borderWidth: 0
                },
            ]
        };

        const polarOptions: any = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom' as const,
                    labels: {
                        font: { size: 12 },
                        filter: (item: any) =>
                            item.text !== 'Критический (25%)' &&
                            item.text !== 'Низкий (50%)' &&
                            item.text !== 'Средний (75%)' &&
                            item.text !== 'Высокий (100%)'
                    }
                },
                title: {
                    display: true,
                    text: 'Результаты по сегментам',
                    font: { size: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: (context: any) => {
                            const value = context.raw as number;
                            return `${context.label}: ${value}%`;
                        }
                    }
                }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        stepSize: 25,
                        callback: (value: any) => value + '%',
                        backdropColor: 'transparent'
                    },
                    grid: {
                        color: '#cbd5e1',
                        lineWidth: 1
                    },
                    angleLines: {
                        color: '#cbd5e1',
                        lineWidth: 1
                    },
                    pointLabels: {
                        font: { size: 12, weight: 'bold' },
                        color: '#1e293b'
                    }
                }
            }
        };

        return <PolarArea data={chartData} options={polarOptions} />;
    }

    // Для других типов чартов
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

    const otherOptions: any = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: chartType !== 'bar' },
            tooltip: {
                callbacks: {
                    label: (context: any) => {
                        const value = context.raw as number;
                        return `${context.label}: ${value}%`;
                    }
                }
            }
        },
        scales: chartType === 'bar' || chartType === 'radar' ? {
            y: {
                max: 100,
                beginAtZero: true,
                ticks: {
                    callback: (value: any) => value + '%'
                }
            }
        } : {}
    };

    switch (chartType) {
        case 'bar':
            return <Bar data={chartData} options={otherOptions} />;
        case 'radar':
            return <Radar data={chartData} options={otherOptions} />;
        case 'doughnut':
            return <Doughnut data={chartData} options={otherOptions} />;
        default:
            return <Bar data={chartData} options={otherOptions} />;
    }
};

export default ChartView;