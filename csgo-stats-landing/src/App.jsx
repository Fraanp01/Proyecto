import React, { useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import AOS from 'aos';
import 'aos/dist/aos.css';
import './App.css';

// Componentes
import Navbar from './components/Navbar';
import Hero from './components/Hero';
import Features from './components/Features';
import Screenshots from './components/Screenshots';
import About from './components/About';
import CallToAction from './components/CallToAction';
import Footer from './components/Footer';

function App() {
  useEffect(() => {
    AOS.init({
      duration: 1000,
      once: false,
      mirror: true,
      easing: 'ease-in-out',
      offset: 120
    });
  }, []);

  return (
    <Router>
      <div className="App">
        <Navbar />
        <Routes>
          <Route path="/" element={
            <>
              <Hero />
              <Features />
              <Screenshots />
              <About />
              <CallToAction />
            </>
          } />
        </Routes>
        <Footer />
      </div>
    </Router>
  );
}

export default App;