import React from 'react';
import './Screenshots.css';

const Screenshots = () => {
  return (
    <section id="screenshots" className="screenshots">
      <h2 data-aos="fade-up">Herramientas</h2>
      <div className="screenshot-list">
        <div className="screenshot-item" data-aos="zoom-in">
          <div className="screenshot-placeholder">
            <i className="fas fa-desktop"></i>
            <p>Vista de Estadísticas</p>
          </div>
        </div>
        <div className="screenshot-item" data-aos="zoom-in" data-aos-delay="100">
          <div className="screenshot-placeholder">
            <i className="fas fa-chart-bar"></i>
            <p>Análisis de Rendimiento</p>
          </div>
        </div>
        <div className="screenshot-item" data-aos="zoom-in" data-aos-delay="200">
          <div className="screenshot-placeholder">
            <i className="fas fa-crosshairs"></i>
            <p>Análisis de Precisión</p>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Screenshots;