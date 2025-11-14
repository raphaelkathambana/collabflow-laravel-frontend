// Hybrid pattern: Import for Vite plugin, then override with window.React
import React, { memo } from 'react';
import { Handle, Position } from '@xyflow/react';

// Use shared React instance from vendor-react.js
const SharedReact = window.React || React;

const StartNode = memo(() => {
    return (
        <div className="relative flex items-center justify-center w-24 h-24 rounded-full shadow-lg" style={{
            backgroundColor: 'var(--color-glaucous)'
        }}>
            <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>

            <Handle
                type="source"
                position={Position.Bottom}
                className="w-3 h-3 bg-white"
            />
        </div>
    );
});

StartNode.displayName = 'StartNode';

export default StartNode;
