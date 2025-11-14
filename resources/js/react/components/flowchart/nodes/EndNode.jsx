// Hybrid pattern: Import for Vite plugin, then override with window.React
import React, { memo } from 'react';
import { Handle, Position } from '@xyflow/react';

// Use shared React instance from vendor-react.js
const SharedReact = window.React || React;

const EndNode = memo(() => {
    return (
        <div className="relative flex items-center justify-center w-24 h-24 rounded-full shadow-lg" style={{
            backgroundColor: 'var(--color-tea-green)'
        }}>
            <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" style={{
                color: '#2d5a3d'
            }}>
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>

            <Handle
                type="target"
                position={Position.Top}
                className="w-3 h-3"
                style={{ backgroundColor: '#2d5a3d' }}
            />
        </div>
    );
});

EndNode.displayName = 'EndNode';

export default EndNode;
