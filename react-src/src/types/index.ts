//react-src/src/types/index.ts
export interface Survey {
    id: string;
    name: string;
    description: string | null;
    chartType: string;
    isActive: boolean;
    createdAt: string;
}

export interface Segment {
    id: string;
    name: string;
    description: string | null;
    icon: string | null;
    color: string;
    orderIndex: number;
}

export interface Question {
    id: string;
    text: string;
    options: Option[];
    segmentId?: string | null;
}

export interface Option {
    id: string;
    text: string;
    score: number;
}