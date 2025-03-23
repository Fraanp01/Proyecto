import React from 'react';

function Features() {
  return (
    <section id="features" className="features">
      <div className="container">
        <h2>Características Clave</h2>
        <div className="feature-list">
          <div className="feature-item">
            <h3>Estadísticas Detalladas</h3>
            <p>Accede a estadísticas en tiempo real sobre tu rendimiento y el de tus amigos.</p>
          </div>
          <div className="feature-item">
            <h3>Seguimiento de Progreso</h3>
            <p>Monitorea tu progreso a lo largo del tiempo y establece metas para mejorar.</p>
          </div>
          <div className="feature-item">
            <h3>Herramientas de Entrenamiento</h3>
            <p>Utiliza nuestras herramientas para practicar y mejorar tus habilidades.</p>
          </div>
        </div>
      </div>
    </section>
  );
}

export default Features;