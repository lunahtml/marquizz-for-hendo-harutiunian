import React, { useState } from 'react';
import type { Question, Option } from '../../types';
//react-src/src/components/SurveyBuilder/QuestionEditor.tsx
interface Props {
    question: Question;
    onSave: (question: Question) => void;
    onClose: () => void;
}

const QuestionEditor: React.FC<Props> = ({ question, onSave, onClose }) => {
    const [text, setText] = useState(question.text);
    const [options, setOptions] = useState<Option[]>(question.options || []);

    const addOption = () => {
        setOptions([...options, { id: '', text: '', score: 0 }]);
    };

    const updateOption = (index: number, field: keyof Option, value: string | number) => {
        const updated = [...options];
        updated[index] = { ...updated[index], [field]: value };
        setOptions(updated);
    };

    const removeOption = (index: number) => {
        setOptions(options.filter((_, i) => i !== index));
    };

    const handleSave = async () => {
        // Сохранить вопрос и варианты через API
        onSave({ ...question, text, options });
        onClose();
    };

    return (
        <div className="question-editor-modal">
            <div className="modal-content">
                <h3>Edit Question</h3>

                <label>Question Text</label>
                <textarea value={text} onChange={(e) => setText(e.target.value)} />

                <label>Options</label>
                {options.map((opt, i) => (
                    <div key={i} className="option-row">
                        <input
                            type="text"
                            value={opt.text}
                            placeholder="Option text"
                            onChange={(e) => updateOption(i, 'text', e.target.value)}
                        />
                        <input
                            type="number"
                            value={opt.score}
                            placeholder="Score"
                            step="0.1"
                            onChange={(e) => updateOption(i, 'score', parseFloat(e.target.value))}
                        />
                        <button onClick={() => removeOption(i)}>✕</button>
                    </div>
                ))}

                <button onClick={addOption}>+ Add Option</button>

                <div className="modal-actions">
                    <button onClick={handleSave}>Save</button>
                    <button onClick={onClose}>Cancel</button>
                </div>
            </div>
        </div>
    );
};

export default QuestionEditor;