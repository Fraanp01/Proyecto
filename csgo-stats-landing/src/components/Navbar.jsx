import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import './Navbar.css';

const Navbar = () => {
  const [scrolled, setScrolled] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      if (window.scrollY > 50) {
        setScrolled(true);
      } else {
        setScrolled(false);
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => {
      window.removeEventListener('scroll', handleScroll);
    };
  }, []);

  return (
    <nav className={`navbar ${scrolled ? 'scrolled' : ''}`}>
      <div className="navbar-container">
        <Link to="/" className="navbar-logo">
          CS:GO Stats <span className="highlight">Tracker</span>
        </Link>

        <div className="menu-icon" onClick={() => setMobileMenuOpen(!mobileMenuOpen)}>
          <i className={mobileMenuOpen ? 'fas fa-times' : 'fas fa-bars'} />
        </div>

        <ul className={`nav-menu ${mobileMenuOpen ? 'active' : ''}`}>
          <li className="nav-item">
            <a href="#features" className="nav-links" onClick={() => setMobileMenuOpen(false)}>
              Características
            </a>
          </li>
          <li className="nav-item">
            <a href="#screenshots" className="nav-links" onClick={() => setMobileMenuOpen(false)}>
              Capturas
            </a>
          </li>
          <li className="nav-item">
            <a href="#about" className="nav-links" onClick={() => setMobileMenuOpen(false)}>
              Acerca de
            </a>
          </li>
          <li className="nav-item">
            <a href="https://github.com/Fraanp01/Proyecto" className="nav-links" target="_blank" rel="noopener noreferrer" onClick={() => setMobileMenuOpen(false)}>
              GitHub
            </a>
          </li>
        </ul>
      </div>
    </nav>
  );
};

export default Navbar;