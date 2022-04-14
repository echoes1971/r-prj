import React from 'react';
import ReactDOM from 'react-dom';
import 'bootstrap/dist/css/bootstrap.min.css';
// import 'bootstrap/dist/js/bootstrap.min.js';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import './index.scss';

import 'draft-js/dist/Draft.css';
import '@draft-js-plugins/static-toolbar/lib/plugin.css'
import '@draft-js-plugins/inline-toolbar/lib/plugin.css'

import App from './App';
import reportWebVitals from './reportWebVitals';

import { app_cfg } from './app.cgf';

ReactDOM.render(
  <React.StrictMode>
    <App endpoint={app_cfg.endpoint} dark_theme={app_cfg.dark_theme} />
  </React.StrictMode>,
  document.getElementById('root')
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
