import React, { useState, useEffect } from 'react';

function Header() {
  const [scrolled, setScrolled] = useState(false);
  
  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 50);
    };
    
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <header className={`header ${scrolled ? 'scrolled' : ''}`}>
      <div className="container header-container">
        <div className="logo">
          <img src="/logo.png" alt="CS:GO Stats Pro" />
          <span>CS:GO Stats Pro</span>
        </div>
        
        <nav>
          <ul>
            <li><a href="#features">Características</a></li>
            <li><a href="#how-it-works">Cómo funciona</a></li>
            <li><a href="#pricing">Precios</a></li>
            <li><a href="#faq">FAQ</a></li>
          </ul>
        </nav>
        
        <div className="cta-buttons">
          <a href="#" className="btn btn-secondary">Iniciar sesión</a>
          <a href="#" className="btn btn-primary">Registrarse</a>
        </div>
      </div>
    </header>
  );
}

export default Header;