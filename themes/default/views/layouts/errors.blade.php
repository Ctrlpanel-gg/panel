<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  @php($website_settings = app(App\Settings\WebsiteSettings::class))

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta content="{{ $website_settings->seo_title }}" property="og:title">
  <meta content="{{ $website_settings->seo_description }}" property="og:description">
  <meta
    content='{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('logo.png') ? asset('storage/logo.png') : asset('images/ctrlpanel_logo.png') }}'
    property="og:image">
  <title>{{ config('app.name', 'Laravel') }}</title>
  <link rel="icon"
        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('favicon.ico') ? asset('storage/favicon.ico') : asset('favicon.ico') }}"
        type="image/x-icon">

  <script src="{{ asset('plugins/alpinejs/3.12.0_cdn.min.js') }}" defer></script>
  <link rel="preload" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
  <noscript>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  </noscript>
  <script src="{{ asset('js/app.js') }}"></script>

  @vite('themes/default/sass/app.scss')

  <script>
    class DeepspaceParticles {
      constructor() {
        this.canvas = document.createElement('canvas');
        this.canvas.style.position = 'fixed';
        this.canvas.style.top = '0';
        this.canvas.style.left = '0';
        this.canvas.style.width = '100%';
        this.canvas.style.height = '100%';
        this.canvas.style.zIndex = '1';
        this.canvas.style.pointerEvents = 'none';
        document.body.appendChild(this.canvas);
        
        this.ctx = this.canvas.getContext('2d');
        this.particles = [];
        this.particleCount = 80; // More particles for richer background
        this.starTypes = ['sparkle', 'fourPoint', 'fivePoint', 'pulsar'];
        this.colors = [
          'rgba(135, 206, 250, 0.85)', // lightskyblue
          'rgba(176, 224, 230, 0.9)',  // powderblue
          'rgba(70, 130, 180, 0.8)',   // steelblue
          'rgba(95, 158, 160, 0.75)'   // cadetblue
        ];
        
        this.init();
        this.animate();
        window.addEventListener('resize', () => this.handleResize());
      }
      
      init() {
        this.handleResize();
        this.createParticles();
      }
      
      handleResize() {
        this.width = window.innerWidth;
        this.height = window.innerHeight;
        this.canvas.width = this.width;
        this.canvas.height = this.height;
        
        if (this.particles.length > 0) {
          this.createParticles();
        }
      }
      
      createParticles() {
        this.particles = [];
        
        for (let i = 0; i < this.particleCount; i++) {
          // Create depth effect with z-index-like positioning
          const depth = Math.random() * 5 + 1;
          const speedFactor = 1 / depth; // Closer stars move faster
          
          this.particles.push({
            x: Math.random() * this.width,
            y: Math.random() * this.height,
            z: depth,
            size: Math.random() * 3 + 1,
            type: this.starTypes[Math.floor(Math.random() * this.starTypes.length)],
            color: this.colors[Math.floor(Math.random() * this.colors.length)],
            velocity: {
              x: (Math.random() * 0.4 - 0.2) * speedFactor,
              y: (Math.random() * 0.4 - 0.2) * speedFactor
            },
            alpha: Math.random() * 0.5 + 0.5,
            pulse: Math.random() * 0.01 + 0.005,
            pulseDuration: Math.random() * 100 + 100,
            twinkle: Math.random() < 0.3 // 30% of stars will twinkle
          });
        }
      }
      
      animate() {
        this.ctx.clearRect(0, 0, this.width, this.height);
        this.updateParticles();
        this.drawParticles();
        requestAnimationFrame(() => this.animate());
      }
      
      updateParticles() {
        const time = Date.now();
        
        this.particles.forEach(particle => {
          // Slow drifting movement
          particle.x += particle.velocity.x;
          particle.y += particle.velocity.y;
          
          // Pulsing glow effect
          const pulseOffset = Math.sin(time * particle.pulse / particle.pulseDuration);
          particle.currentAlpha = particle.alpha * (0.7 + pulseOffset * 0.3);
          
          // Size variation based on depth and pulsing
          particle.currentSize = (particle.size / particle.z) * (0.8 + pulseOffset * 0.4);
          
          // Twinkle effect for some stars
          if (particle.twinkle) {
            particle.currentAlpha *= (0.7 + Math.sin(time * 0.01) * 0.3);
          }
          
          // Reset position if out of bounds
          if (particle.x < -50) particle.x = this.width + 50;
          if (particle.x > this.width + 50) particle.x = -50;
          if (particle.y < -50) particle.y = this.height + 50;
          if (particle.y > this.height + 50) particle.y = -50;
        });
      }
      
      drawStar(x, y, size, type, color) {
        this.ctx.save();
        this.ctx.translate(x, y);
        
        // Extract base color and alpha from the rgba value
        const colorParts = color.match(/rgba\((\d+),\s*(\d+),\s*(\d+),\s*([0-9.]+)\)/);
        const baseColor = colorParts ? `rgba(${colorParts[1]}, ${colorParts[2]}, ${colorParts[3]},` : 'rgba(135, 206, 250,';
        
        if (type === 'sparkle') {
          // Enhanced sparkle with more rays
          this.ctx.beginPath();
          this.ctx.fillStyle = color;
          
          // Draw horizontal line
          this.ctx.fillRect(-size * 2, -size/3, size * 4, size/1.5);
          
          // Draw vertical line
          this.ctx.fillRect(-size/3, -size * 2, size/1.5, size * 4);
          
          // Draw diagonal lines
          this.ctx.save();
          this.ctx.rotate(Math.PI / 4);
          this.ctx.fillRect(-size * 1.5, -size/4, size * 3, size/2);
          this.ctx.fillRect(-size/4, -size * 1.5, size/2, size * 3);
          this.ctx.restore();
          
          // Add center glow
          const glowGradient = this.ctx.createRadialGradient(0, 0, 0, 0, 0, size * 3);
          glowGradient.addColorStop(0, color);
          glowGradient.addColorStop(1, `${baseColor} 0)`);
          
          this.ctx.beginPath();
          this.ctx.fillStyle = glowGradient;
          this.ctx.arc(0, 0, size * 3, 0, Math.PI * 2);
          this.ctx.fill();
        } 
        else if (type === 'fourPoint') {
          // Enhanced four-point star
          this.ctx.beginPath();
          for (let i = 0; i < 4; i++) {
            this.ctx.rotate(Math.PI / 2);
            this.ctx.lineTo(0, 0 - size * 2.5);
            this.ctx.lineTo(0 + size * 0.7, 0 - size * 0.7);
          }
          this.ctx.closePath();
          this.ctx.fillStyle = color;
          this.ctx.fill();
          
          // Add stronger glow
          const glowGradient = this.ctx.createRadialGradient(0, 0, 0, 0, 0, size * 3);
          glowGradient.addColorStop(0, color);
          glowGradient.addColorStop(0.5, `${baseColor} 0.3)`);
          glowGradient.addColorStop(1, `${baseColor} 0)`);
          
          this.ctx.beginPath();
          this.ctx.fillStyle = glowGradient;
          this.ctx.arc(0, 0, size * 3, 0, Math.PI * 2);
          this.ctx.fill();
        } 
        else if (type === 'fivePoint') {
          // Enhanced five-point star
          this.ctx.beginPath();
          for (let i = 0; i < 5; i++) {
            this.ctx.rotate(Math.PI * 2 / 5);
            this.ctx.lineTo(0, 0 - size * 2.5);
            this.ctx.rotate(Math.PI * 2 / 10);
            this.ctx.lineTo(0 - size * 0.7, 0 - size * 0.7);
          }
          this.ctx.closePath();
          this.ctx.fillStyle = color;
          this.ctx.fill();
          
          // Add stronger glow
          const glowGradient = this.ctx.createRadialGradient(0, 0, 0, 0, 0, size * 3);
          glowGradient.addColorStop(0, color);
          glowGradient.addColorStop(0.5, `${baseColor} 0.3)`);
          glowGradient.addColorStop(1, `${baseColor} 0)`);
          
          this.ctx.beginPath();
          this.ctx.fillStyle = glowGradient;
          this.ctx.arc(0, 0, size * 3, 0, Math.PI * 2);
          this.ctx.fill();
        }
        else if (type === 'pulsar') {
          // Pulsar effect - a circular star with pulsing rings
          // Inner circle
          this.ctx.beginPath();
          this.ctx.arc(0, 0, size * 0.8, 0, Math.PI * 2);
          this.ctx.fillStyle = color;
          this.ctx.fill();
          
          // Outer rings - semi-transparent
          const time = Date.now() * 0.001;
          const ringCount = 3;
          const maxRadius = size * 5;
          
          for (let i = 0; i < ringCount; i++) {
            const phase = (i / ringCount) * Math.PI * 2;
            const pulseOffset = Math.sin(time + phase) * 0.5 + 0.5; // 0 to 1
            const ringRadius = size * (1.5 + pulseOffset * 3.5);
            const ringAlpha = 0.3 * (1 - pulseOffset * 0.7);
            
            this.ctx.beginPath();
            this.ctx.arc(0, 0, ringRadius, 0, Math.PI * 2);
            this.ctx.lineWidth = size * 0.2;
            this.ctx.strokeStyle = baseColor + ringAlpha + ')';
            this.ctx.stroke();
          }
          
          // Central glow
          const glowGradient = this.ctx.createRadialGradient(0, 0, 0, 0, 0, size * 4);
          glowGradient.addColorStop(0, color);
          glowGradient.addColorStop(0.5, `${baseColor} 0.2)`);
          glowGradient.addColorStop(1, `${baseColor} 0)`);
          
          this.ctx.beginPath();
          this.ctx.fillStyle = glowGradient;
          this.ctx.arc(0, 0, size * 4, 0, Math.PI * 2);
          this.ctx.fill();
        }
        
        this.ctx.restore();
      }
      
      drawParticles() {
        // Sort particles by z-index to create depth effect
        this.particles.sort((a, b) => b.z - a.z);
        
        this.particles.forEach(particle => {
          const alpha = particle.currentAlpha.toFixed(2);
          const colorWithAlpha = particle.color.replace(/[^,]+(?=\))/, alpha);
          this.drawStar(
            particle.x,
            particle.y,
            particle.currentSize,
            particle.type,
            colorWithAlpha
          );
        });
      }
    }

    // Create cosmic elements class
    class CosmicElements {
      constructor() {
        // Create nebula element
        this.createNebula();
        
        // Create crescent moon with enhanced aura
        this.moon = document.createElement('div');
        this.moon.className = 'cosmic-moon';
        
        // Create the aura element
        this.aura = document.createElement('div');
        this.aura.className = 'cosmic-aura';
        this.moon.appendChild(this.aura);
        
        document.body.appendChild(this.moon);
        
        // Create stars around the moon with enhanced glow
        const starCount = 15; // Increased star count
        for (let i = 0; i < starCount; i++) {
          const star = document.createElement('div');
          star.className = 'cosmic-star';
          
          // Create random positioning around the moon in a wider radius
          const angle = (i / starCount) * Math.PI * 2;
          const distance = 100 + Math.random() * 120; // Increased distance
          const size = 4 + Math.random() * 8;
          
          star.style.width = `${size}px`;
          star.style.height = `${size}px`;
          star.style.right = `calc(10% + ${Math.cos(angle) * distance}px)`;
          star.style.top = `calc(15% + ${Math.sin(angle) * distance}px)`;
          
          // Randomize animation delay and duration
          star.style.animationDelay = `${Math.random() * 3}s`;
          star.style.animationDuration = `${3 + Math.random() * 2}s`;
          
          document.body.appendChild(star);
        }
        
        // Create floating dust particles
        this.createDustParticles();
      }
      
      createNebula() {
        const nebula = document.createElement('div');
        nebula.className = 'cosmic-nebula';
        document.body.appendChild(nebula);
      }
      
      createDustParticles() {
        const dustCount = 30;
        for (let i = 0; i < dustCount; i++) {
          const dust = document.createElement('div');
          dust.className = 'cosmic-dust';
          
          // Random positions
          dust.style.left = `${Math.random() * 100}%`;
          dust.style.top = `${Math.random() * 100}%`;
          
          // Random sizes
          const size = 1 + Math.random() * 3;
          dust.style.width = `${size}px`;
          dust.style.height = `${size}px`;
          
          // Random opacity
          dust.style.opacity = (0.1 + Math.random() * 0.4).toString();
          
          // Random animation
          dust.style.animationDuration = `${20 + Math.random() * 40}s`;
          dust.style.animationDelay = `${Math.random() * 5}s`;
          
          document.body.appendChild(dust);
        }
      }
    }

    // Initialize on window load
    window.addEventListener('load', () => {
      try {
        new DeepspaceParticles();
        new CosmicElements();
      } catch (e) {
        console.warn('Cosmic initialization failed:', e);
      }
    });
  </script>

  <style>
    html {
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      overflow-x: hidden;
      scroll-behavior: smooth;
    }

    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      width: 100%;
      background-color: #030711;
      background: linear-gradient(135deg, #0a0f29 0%, #020307 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Oxanium', system-ui, -apple-system, sans-serif;
      overflow-x: hidden;
      position: relative;
      box-sizing: border-box;
      -webkit-font-smoothing: antialiased;
    }

    *, *::before, *::after {
      box-sizing: inherit;
    }

    /* Cosmic Nebula Background */
    .cosmic-nebula {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(
        ellipse at 70% 20%, 
        rgba(65, 88, 208, 0.15) 0%, 
        rgba(25, 49, 94, 0.1) 40%, 
        rgba(3, 7, 17, 0) 80%
      );
      pointer-events: none;
      z-index: 1;
      opacity: 0.8;
    }

    /* Cosmic Moon Element */
    .cosmic-moon {
      position: fixed;
      top: 15%;
      right: 10%;
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: linear-gradient(135deg, #a6d8ff 0%, #87cefa 100%);
      box-shadow: 
        0 0 60px rgba(135, 206, 250, 0.8),
        0 0 100px rgba(135, 206, 250, 0.6),
        0 0 160px rgba(173, 216, 230, 0.4);
      z-index: 2;
      animation: moonPulse 8s ease-in-out infinite;
      pointer-events: none;
      overflow: visible;
    }

    .cosmic-aura {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 250%;
      height: 250%;
      background: radial-gradient(
        circle,
        rgba(166, 216, 255, 0.5) 0%,
        rgba(135, 206, 250, 0.2) 30%,
        rgba(135, 206, 250, 0.1) 50%,
        rgba(135, 206, 250, 0) 70%
      );
      border-radius: 50%;
      animation: auraGlow 6s ease-in-out infinite alternate;
    }

    .cosmic-moon::before {
      content: '';
      position: absolute;
      top: -10px;
      left: 25px;
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background-color: #030711;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    }

    /* Cosmic Star styling */
    .cosmic-star {
      position: fixed;
      width: 8px;
      height: 8px;
      background-color: #a6d8ff;
      clip-path: polygon(
        50% 0%,
        61% 35%,
        98% 35%,
        68% 57%,
        79% 91%,
        50% 70%,
        21% 91%,
        32% 57%,
        2% 35%,
        39% 35%
      );
      z-index: 2;
      animation: starTwinkle 4s ease-in-out infinite alternate;
      pointer-events: none;
      box-shadow: 
        0 0 15px rgba(135, 206, 250, 0.8),
        0 0 30px rgba(135, 206, 250, 0.6),
        0 0 45px rgba(135, 206, 250, 0.3);
    }

    /* Cosmic Dust Particles */
    .cosmic-dust {
      position: fixed;
      background-color: rgba(255, 255, 255, 0.8);
      border-radius: 50%;
      z-index: 1;
      pointer-events: none;
      animation: dustFloat linear infinite;
    }

    @keyframes dustFloat {
      0% {
        transform: translate(0, 0);
      }
      25% {
        transform: translate(20px, 10px);
      }
      50% {
        transform: translate(0, 20px);
      }
      75% {
        transform: translate(-20px, 10px);
      }
      100% {
        transform: translate(0, 0);
      }
    }

    @keyframes auraGlow {
      0% {
        opacity: 0.5;
        transform: translate(-50%, -50%) scale(1);
      }
      100% {
        opacity: 0.8;
        transform: translate(-50%, -50%) scale(1.2);
      }
    }

    @keyframes moonPulse {
      0% {
        opacity: 0.9;
        box-shadow: 
          0 0 60px rgba(135, 206, 250, 0.6),
          0 0 100px rgba(135, 206, 250, 0.4),
          0 0 160px rgba(173, 216, 230, 0.2);
      }
      50% {
        opacity: 1;
        box-shadow: 
          0 0 80px rgba(135, 206, 250, 0.8),
          0 0 120px rgba(135, 206, 250, 0.6),
          0 0 200px rgba(173, 216, 230, 0.4);
      }
      100% {
        opacity: 0.9;
        box-shadow: 
          0 0 60px rgba(135, 206, 250, 0.6),
          0 0 100px rgba(135, 206, 250, 0.4),
          0 0 160px rgba(173, 216, 230, 0.2);
      }
    }

    @keyframes starTwinkle {
      0%, 100% {
        opacity: 1;
        transform: scale(1) rotate(0deg);
      }
      50% {
        opacity: 0.4;
        transform: scale(0.8) rotate(180deg);
      }
    }

    .animate-background {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      background: radial-gradient(
        circle at 70% 20%, 
        rgba(25, 49, 94, 0.2) 0%, 
        rgba(3, 7, 17, 0.9) 50%,
        rgba(3, 7, 17, 1) 100%
      );
      pointer-events: none;
    }

    main {
      position: relative;
      width: 100%;
      z-index: 10;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 1rem;
      box-sizing: border-box;
      pointer-events: auto;
    }
    
    .error-container {
      width: min(95%, 1024px);
    }

    .error-card {
      @apply glass-panel glass-morphism;
      display: grid;
      grid-template-columns: minmax(240px, 320px) 1fr;
      gap: 2rem;
      position: relative;
      overflow: hidden;
      animation: cardFloat 6s ease-in-out infinite;
      border: 1px solid rgba(135, 206, 250, 0.15);
      box-shadow: 
        0 10px 30px rgba(0, 0, 0, 0.2),
        0 0 10px rgba(135, 206, 250, 0.1),
        0 0 20px rgba(135, 206, 250, 0.05);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      z-index: 20;
      transform-style: preserve-3d;
      perspective: 1000px;
    }

    .error-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 50%;
      height: 100%;
      background: linear-gradient(
        90deg, 
        transparent, 
        rgba(135, 206, 250, 0.05), 
        transparent
      );
      z-index: 1;
      animation: shimmer 6s infinite;
      pointer-events: none;
    }

    .error-header {
      padding: 3rem 2rem;
      margin: 0;
      border-right: 1px solid rgba(135, 206, 250, 0.15);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      position: relative;
      overflow: hidden;
      background: rgba(3, 7, 17, 0.95);
      transform-style: preserve-3d;
    }

    .error-header::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(
        circle at center,
        rgba(25, 49, 94, 0.2) 0%,
        rgba(3, 7, 17, 0) 70%
      );
      z-index: -1;
    }

    .error-code {
      @apply text-primary-100;
      font-size: 7rem;
      line-height: 1;
      margin: 0;
      background: linear-gradient(135deg, #87CEFA 0%, #ADD8E6 50%, #87CEFA 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 
        0 0 30px rgba(135, 206, 250, 0.5),
        0 0 60px rgba(135, 206, 250, 0.3);
      letter-spacing: -2px;
      animation: glowPulse 3s ease-in-out infinite;
      position: relative;
      transform: translateZ(20px);
    }

    .error-header h1 {
      @apply text-primary-100;
      font-size: 1.5rem;
      margin: 1rem 0 0;
      font-weight: 600;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
      letter-spacing: -0.5px;
      max-width: 280px;
      text-align: center;
      position: relative;
      transform: translateZ(10px);
    }

    .error-body {
      padding: 3rem 2.5rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
      text-align: left;
      position: relative;
      overflow: hidden;
    }

    .error-body::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(
        circle at 70% 50%,
        rgba(25, 49, 94, 0.1) 0%,
        rgba(3, 7, 17, 0) 70%
      );
      z-index: -1;
    }

    .error-body p {
      @apply text-primary-300;
      font-size: 1.5rem;
      line-height: 1.8;
      margin: 0 0 2rem;
      font-weight: 400;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
      position: relative;
      transform: translateZ(5px);
    }

    .error-exception {
      @apply bg-black/30 p-4 rounded-lg mb-6 text-white text-sm overflow-x-auto;
      border: 1px solid rgba(135, 206, 250, 0.1);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      max-height: 150px;
      overflow-y: auto;
      position: relative;
      transform: translateZ(5px);
    }

    .error-exception::-webkit-scrollbar {
      width: 4px;
      height: 4px;
    }

    .error-exception::-webkit-scrollbar-thumb {
      background-color: rgba(135, 206, 250, 0.3);
      border-radius: 2px;
    }

    .error-exception::-webkit-scrollbar-track {
      background-color: rgba(0, 0, 0, 0.2);
      border-radius: 2px;
    }

    .home-btn {
      @apply action-btn info;
      font-size: 1rem;
      padding: 0.75rem 2rem;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      margin: 0 auto;
      position: relative;
      transform: translateZ(10px);
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .home-btn i {
      font-size: 1.125rem;
      transition: transform 0.3s ease;
    }

    .home-btn:hover {
      transform: translateZ(15px) scale(1.05);
    }

    .home-btn:hover i {
      transform: translateX(-4px);
    }

    @media (max-width: 768px) {
      .error-card {
        grid-template-columns: 1fr;
        gap: 0;
        max-width: 95%;
      }

      .error-header {
        padding: 2.5rem 1.5rem;
        border-right: 0;
        border-bottom: 1px solid rgba(135, 206, 250, 0.15);
      }

      .error-code {
        font-size: 5rem;
      }

      .error-header h1 {
        font-size: 1.25rem;
      }

      .error-body {
        padding: 2rem 1.5rem;
        text-align: center;
      }

      .error-body p {
        font-size: 1.125rem;
      }
      
      .cosmic-moon {
        top: 10%;
        right: 8%;
        width: 80px;
        height: 80px;
      }
      
      .cosmic-moon::before {
        width: 80px;
        height: 80px;
        left: 20px;
      }
    }

    @keyframes cardFloat {
      0%, 100% {
        transform: translateY(0) rotateX(2deg);
      }
      50% {
        transform: translateY(-10px) rotateX(-2deg);
      }
    }

    @keyframes shimmer {
      0% {
        transform: translateX(0%);
      }
      100% {
        transform: translateX(200%);
      }
    }

    @keyframes glowPulse {
      0%, 100% {
        opacity: 1;
        text-shadow: 
          0 0 30px rgba(135, 206, 250, 0.5),
          0 0 60px rgba(135, 206, 250, 0.3);
      }
      50% {
        opacity: 0.8;
        text-shadow: 
          0 0 40px rgba(135, 206, 250, 0.7),
          0 0 80px rgba(135, 206, 250, 0.4);
      }
    }

    @media (max-width: 480px) {
      .error-code {
        font-size: 4.5rem;
      }

      .error-header h1 {
        font-size: 1.25rem;
      }

      .error-body p {
        font-size: 1rem;
      }
      
      .error-card {
        padding: 1.5rem;
      }
      
      .home-btn {
        padding: 0.625rem 1.5rem;
        font-size: 0.875rem;
      }
      
      .cosmic-moon {
        width: 60px;
        height: 60px;
      }
      
      .cosmic-moon::before {
        width: 60px;
        height: 60px;
        left: 15px;
      }
    }
  </style>
</head>
<body class="bg-primary-950">
  <div class="animate-background"></div>
  
  <main>
    <div class="error-container">
      <div class="error-card">
        <div class="error-header">
          <div class="error-code">{{ $errorCode }}</div>
          <h1>{{ $title }}</h1>
        </div>
        
        <div class="error-body">
          <p>{{ $message }}</p>
          
          @if($exception ?? false && Auth::user()->can('errors.view'))
            <div class="error-exception">
              <div class="exception-heading">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Exception Details</span>
              </div>
              <div class="exception-content">
                {{ $exception->getMessage() }}
              </div>
            </div>
          @endif
          
          @if($homeLink ?? false)
            <div class="flex justify-center mt-8">
              <a href="{{ route('home') }}" class="home-btn">
                <span class="btn-glow"></span>
                <i class="fas fa-chevron-left"></i>
                <span>{{ __('Return Home') }}</span>
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>
    
    <!-- Additional cosmic elements -->
    <div class="cosmic-rings"></div>
    <div class="floating-asteroids"></div>
  </main>

  <style>
    /* Enhanced error card styling */
    .error-card {
      backdrop-filter: blur(15px);
      box-shadow: 
        0 15px 40px rgba(0, 0, 0, 0.3),
        0 0 20px rgba(135, 206, 250, 0.15),
        0 0 40px rgba(135, 206, 250, 0.08),
        inset 0 0 2px rgba(173, 216, 230, 0.2);
      border-radius: 16px;
      border: 1px solid rgba(135, 206, 250, 0.2);
      animation: cardFloat 8s ease-in-out infinite, cardGlow 10s infinite alternate;
    }
    
    /* Enhanced error code styling */
    .error-code {
      background: linear-gradient(135deg, #87CEFA 10%, #E0FFFF 40%, #87CEFA 60%, #B0E2FF 90%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 
        0 0 40px rgba(135, 206, 250, 0.7),
        0 0 80px rgba(135, 206, 250, 0.4);
      font-weight: 700;
      transform: translateZ(25px);
      letter-spacing: -2px;
      font-size: 8rem;
    }
    
    /* Enhanced title styling */
    .error-header h1 {
      background: linear-gradient(to right, #FFFFFF, #B0E2FF, #FFFFFF);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      transform: translateZ(15px);
      letter-spacing: 0.5px;
      font-weight: 600;
    }
    
    /* Exception box styling */
    .error-exception {
      background: rgba(0, 0, 0, 0.4);
      border-radius: 12px;
      border: 1px solid rgba(135, 206, 250, 0.15);
      box-shadow: 
        0 4px 15px rgba(0, 0, 0, 0.2),
        0 0 10px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transform: translateZ(10px);
    }
    
    .exception-heading {
      background: rgba(25, 49, 94, 0.5);
      padding: 0.75rem 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: #ADD8E6;
      font-weight: 500;
      border-bottom: 1px solid rgba(135, 206, 250, 0.2);
    }
    
    .exception-content {
      padding: 1rem;
      color: rgba(255, 255, 255, 0.8);
      font-family: 'Consolas', 'Monaco', monospace;
      max-height: 150px;
      overflow-y: auto;
    }
    
    /* Enhanced button styling */
    .home-btn {
      background: rgba(25, 49, 94, 0.6);
      color: #ADD8E6;
      border: 1px solid rgba(135, 206, 250, 0.3);
      border-radius: 50px;
      padding: 0.875rem 2.5rem;
      font-weight: 500;
      position: relative;
      overflow: hidden;
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      transform: translateZ(10px);
      box-shadow: 
        0 4px 15px rgba(0, 0, 0, 0.2),
        0 0 20px rgba(135, 206, 250, 0.1);
    }
    
    .home-btn:hover {
      background: rgba(35, 69, 114, 0.8);
      transform: translateZ(15px) scale(1.05);
      box-shadow: 
        0 7px 20px rgba(0, 0, 0, 0.3),
        0 0 30px rgba(135, 206, 250, 0.2);
    }
    
    .btn-glow {
      position: absolute;
      width: 30px;
      height: 100%;
      top: 0;
      left: -100px;
      background: linear-gradient(
        90deg, 
        transparent, 
        rgba(173, 216, 230, 0.3), 
        transparent
      );
      animation: btnShimmer 3s infinite;
    }
    
    /* Additional cosmic elements */
    .cosmic-rings {
      position: fixed;
      top: 50%;
      left: 20%;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      border: 1px solid rgba(135, 206, 250, 0.1);
      box-shadow: 0 0 30px rgba(135, 206, 250, 0.05);
      transform: translate(-50%, -50%);
      z-index: 1;
      pointer-events: none;
    }
    
    .cosmic-rings::before,
    .cosmic-rings::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }
    
    .cosmic-rings::before {
      width: 300px;
      height: 300px;
      border: 2px solid rgba(173, 216, 230, 0.15);
      box-shadow: 0 0 20px rgba(173, 216, 230, 0.1);
      animation: ringPulse 15s linear infinite;
    }
    
    .cosmic-rings::after {
      width: 200px;
      height: 200px;
      border: 2px solid rgba(176, 224, 230, 0.2);
      box-shadow: 0 0 15px rgba(176, 224, 230, 0.15);
      animation: ringPulse 12s linear infinite reverse;
    }
    
    .floating-asteroids {
      position: fixed;
      bottom: 10%;
      right: 15%;
      z-index: 1;
      pointer-events: none;
    }
    
    .floating-asteroids::before,
    .floating-asteroids::after {
      content: '';
      position: absolute;
      background: radial-gradient(circle, rgba(135, 206, 250, 0.3) 0%, rgba(135, 206, 250, 0) 70%);
      border-radius: 50%;
    }
    
    .floating-asteroids::before {
      width: 30px;
      height: 30px;
      animation: asteroidFloat 25s ease-in-out infinite;
    }
    
    .floating-asteroids::after {
      width: 20px;
      height: 20px;
      top: 40px;
      left: 60px;
      animation: asteroidFloat 15s ease-in-out infinite 5s;
    }
    
    /* Additional animations */
    @keyframes cardGlow {
      0% {
        box-shadow: 
          0 15px 40px rgba(0, 0, 0, 0.3),
          0 0 20px rgba(135, 206, 250, 0.15),
          0 0 40px rgba(135, 206, 250, 0.08);
      }
      100% {
        box-shadow: 
          0 15px 40px rgba(0, 0, 0, 0.3),
          0 0 30px rgba(135, 206, 250, 0.25),
          0 0 60px rgba(135, 206, 250, 0.15);
      }
    }
    
    @keyframes btnShimmer {
      0% {
        left: -100px;
      }
      20% {
        left: 120%;
      }
      100% {
        left: 120%;
      }
    }
    
    @keyframes ringPulse {
      0% {
        transform: translate(-50%, -50%) rotate(0deg);
      }
      100% {
        transform: translate(-50%, -50%) rotate(360deg);
      }
    }
    
    @keyframes asteroidFloat {
      0%, 100% {
        transform: translate(0, 0);
        opacity: 0.6;
      }
      25% {
        transform: translate(-20px, 30px);
        opacity: 0.8;
      }
      50% {
        transform: translate(30px, 50px);
        opacity: 0.6;
      }
      75% {
        transform: translate(10px, 10px);
        opacity: 0.7;
      }
    }
    
    /* Media query improvements */
    @media (max-width: 768px) {
      .error-code {
        font-size: 6rem;
      }
      
      .cosmic-rings {
        width: 300px;
        height: 300px;
        left: 0;
        opacity: 0.5;
      }
      
      .cosmic-rings::before {
        width: 220px;
        height: 220px;
      }
      
      .cosmic-rings::after {
        width: 140px;
        height: 140px;
      }
    }
    
    @media (max-width: 480px) {
      .error-code {
        font-size: 5rem;
      }
      
      .exception-content {
        font-size: 0.875rem;
      }
      
      .cosmic-rings {
        opacity: 0.3;
      }
    }
  </style>
</body>
</html>

