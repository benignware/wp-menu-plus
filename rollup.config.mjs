import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import babel from '@rollup/plugin-babel';
import css from 'rollup-plugin-css-only';

export default [
    {
        input: 'features/menu-icon/menu-icon-editor.jsx', // Editor script entry point
        output: [{
            file: 'dist/menuplus-icon-editor.js',
            format: 'iife',
            name: 'MeunPlusIconEditor',
            globals: {
                'react': 'wp.element', // Use WordPress' React version
                'react-dom': 'wp.element' // Adjust if necessary
            }
        }],
        plugins: [
            resolve(),
            commonjs(),
            babel({
                babelHelpers: 'bundled',
                presets: ['@babel/preset-react'],
                exclude: 'node_modules/**'
            }),
            css({ output: 'menuplus-icon-editor.css' }),
        ],
        external: ['react', 'react-dom'] // Exclude React from the editor bundle
    }
];
