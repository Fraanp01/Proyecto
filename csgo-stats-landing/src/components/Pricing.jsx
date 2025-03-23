import React from 'react';

function Pricing() {
  return (
    <section id="pricing" className="pricing">
      <div className="container">
        <h2>Precios</h2>
        <div className="pricing-plan">
          <h3>Plan Básico</h3>
          <p>Acceso a estadísticas básicas y seguimiento de progreso.</p>
          <span>$9.99/mes</span>
          <a href="#" className="btn btn-primary">Elegir Plan</a>
        </div>
        <div className="pricing-plan">
          <h3>Plan Premium</h3>
          <p>Acceso completo a todas las características y herramientas de entrenamiento.</p>
          <span>$19.99/mes</span>
          <a href="#" className="btn btn-primary">Elegir Plan</a>
        </div>
      </div>
    </section>
  );
}

export default Pricing;