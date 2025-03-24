import React from 'react';
import './CallToAction.css';

const CallToAction = () => {
  return (
    <section id="call-to-action" className="call-to-action">
      <div className="container">
        <h2 data-aos="fade-up">¡No esperes más!</h2>
        <p data-aos="fade-up" data-aos-delay="100">Únete a nuestra comunidad y lleva tu juego al siguiente nivel.</p>
        <a href="#features" className="btn btn-primary" data-aos="fade-up" data-aos-delay="200">Comienza Ahora</a>
      </div>
    </section>
  );
};

export default CallToAction;