import React from 'react';
function Hero() {
  return (
    <section className="hero">
      <div className="container hero-container">
        <div className="hero-content">
          <h1>Eleva tu juego en CS:GO con análisis profesional</h1>
          <p>
            CS:GO Stats Pro te ofrece estadísticas detalladas, seguimiento de progreso y 
            herramientas de entrenamiento para ayudarte a mejorar tu rendimiento 
            y alcanzar el siguiente nivel.
          </p>
          <div className="hero-cta">
            <a href="#" className="btn btn-primary btn-large">Comenzar gratis</a>
            <a href="#demo" className="btn btn-text">Ver demo <i className="fas fa-play-circle"></i></a>
          </div>
        </div>
        
        <div className="hero-image">
          <img src="/hero-dashboard.png" alt="Dashboard de CS:GO Stats Pro" />
        </div>
      </div>
      
       </section>
  );
}

export default Hero;