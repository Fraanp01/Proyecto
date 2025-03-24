import React from 'react';
import './Features.css';

const Features = () => {
  return (
    <section id="features" className="features">
      <div className="container">
        <h2 data-aos="fade-down">Características Principales</h2>
        <div className="feature-list">
          <div className="feature-item" data-aos="fade-up" data-aos-delay="100">
            <div className="feature-icon">
              <i className="fas fa-chart-line"></i>
            </div>
            <div className="feature-content">
              <h3>Estadísticas en Tiempo Real</h3>
              <p>Accede a estadísticas actualizadas al instante para mejorar tu rendimiento.</p>
            </div>
          </div>
          <div className="feature-item" data-aos="fade-up" data-aos-delay="300">
            <div className="feature-icon">
              <i className="fas fa-crosshairs"></i>
            </div>
            <div className="feature-content">
              <h3>Análisis de Rendimiento</h3>
              <p>Recibe análisis detallados de tus partidas y áreas de mejora.</p>
            </div>
          </div>
          <div className="feature-item" data-aos="fade-up" data-aos-delay="500">
            <div className="feature-icon">
              <i className="fas fa-user-friends"></i>
            </div>
            <div className="feature-content">
              <h3>Conexión con coach</h3>
              <p>Obten feedback de tu coach fácilmente.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Features;