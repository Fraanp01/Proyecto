import React from 'react';
import './Hero.css';
import backgroundImage from '../assets/cs21.jpeg'; // Importamos la imagen

const Hero = () => {
  return (
    <header 
      className="hero" 
      id="home" 
      style={{ backgroundImage: `url(${backgroundImage})` }} // Aplicamos la imagen como fondo
    >
      <div className="hero-content" data-aos="fade-up">
        <h1>Bienvenido a CSTeamBuild</h1>
        <p>Mejora tu juego con estadísticas detalladas y análisis de rendimiento.</p>
        <div className="hero-buttons">
          <a href="#features" className="btn btn-primary">Descubre Más</a>
          <a href="#call-to-action" className="btn btn-secondary">Comenzar Ahora</a>
        </div>
      </div>
    </header>
  );
};

export default Hero;