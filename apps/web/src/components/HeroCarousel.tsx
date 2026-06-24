import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { ChevronDown } from 'lucide-react';
import { HomeHeroWave } from './home/HomeSectionDivider';
import { publicAsset } from '../lib/publicAsset';

const ROTATE_MS = 6000;

type HeroSlide = {
  image: string;
  alt: string;
  title: string;
  meta: string;
  tagline: string;
  ctaLabel: string;
  ctaTo: string;
  highlights: string[];
};

const VALUE_CHATS = [
  'Más de 12 especialidades médicas en un solo lugar.',
  'Citas en línea y pago seguro, sin filas ni papeleo.',
  'Emergencia 24/7 con equipo humano y tecnología clínica.',
  'Trato cercano antes, durante y después de tu atención.',
];

const slides: HeroSlide[] = [
  {
    image: publicAsset('img/hero/slide-consulta.jpg'),
    alt: 'Profesional de salud en consulta médica',
    title: 'Clínica NovaSalud',
    meta: 'Atención ambulatoria y hospitalaria · Huancayo',
    tagline: 'Salud de primer nivel con un enfoque humano y tecnología clínica.',
    ctaLabel: 'Agendar cita',
    ctaTo: '/cita',
    highlights: [
      'Diagnóstico oportuno con médicos de confianza.',
      'Consultas presenciales y seguimiento personalizado.',
    ],
  },
  {
    image: publicAsset('img/hero/slide-equipo.jpg'),
    alt: 'Equipo médico en trabajo colaborativo',
    title: 'Especialistas integrados',
    meta: 'Cardiología, pediatría, traumatología y más',
    tagline: 'Un equipo calificado te acompaña antes, durante y después de tu atención.',
    ctaLabel: 'Ver especialidades',
    ctaTo: '/especialidades',
    highlights: [
      'Profesionales coordinados para tu caso clínico.',
      'Especialidades que trabajan en conjunto por tu salud.',
    ],
  },
  {
    image: publicAsset('img/hero/slide-hospital.jpg'),
    alt: 'Instalaciones hospitalarias modernas',
    title: 'Tecnología y confianza',
    meta: 'Laboratorio, imágenes y emergencia 24 horas',
    tagline: 'Resultados confiables, diagnóstico oportuno y atención cuando más lo necesitas.',
    ctaLabel: 'Pagar en línea',
    ctaTo: '/pagar',
    highlights: [
      'Instalaciones modernas y equipos de última generación.',
      'Laboratorio e imágenes con resultados rápidos.',
    ],
  },
];

export default function HeroCarousel() {
  const [active, setActive] = useState(0);

  useEffect(() => {
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced || slides.length < 2) return undefined;

    const id = window.setInterval(() => {
      setActive((i) => (i + 1) % slides.length);
    }, ROTATE_MS);

    return () => window.clearInterval(id);
  }, []);

  const slide = slides[active];
  const chats = [...slide.highlights, ...VALUE_CHATS].slice(0, 4);

  return (
    <section
      id="inicio"
      className="hero-banner hero-banner--fullscreen"
      aria-label="Presentación principal"
      aria-roledescription="carrusel"
    >
      <div className="hero-banner__slides" aria-hidden="true">
        {slides.map((s, i) => (
          <div
            key={s.image}
            className={`hero-banner__slide${i === active ? ' is-active' : ''}`}
            style={{ backgroundImage: `url(${s.image})` }}
            role="img"
            aria-label={s.alt}
          />
        ))}
      </div>

      <div className="hero-banner__scrim" aria-hidden="true" />

      <div className="container hero-banner__layout hero-banner__layout--fullscreen">
        <div className="hero-banner__primary">
          <h1 className="hero-banner__title">{slide.title}</h1>
          <p className="hero-banner__meta">{slide.meta}</p>

          <ul className="hero-banner__chats" key={active} aria-label="Por qué elegir NovaSalud">
            {chats.map((text, i) => (
              <li
                key={`${active}-${i}`}
                className="hero-banner__chat"
                style={{ animationDelay: `${i * 0.12}s` }}
              >
                <span className="hero-banner__chat-avatar" aria-hidden="true">
                  NS
                </span>
                <p>{text}</p>
              </li>
            ))}
          </ul>
        </div>

        <div className="hero-banner__secondary">
          <p className="hero-banner__tagline">{slide.tagline}</p>
          <Link className="btn btn--hero" to={slide.ctaTo}>
            {slide.ctaLabel}
          </Link>
        </div>
      </div>

      <div className="hero-banner__dots" role="tablist" aria-label="Diapositivas del banner">
        {slides.map((s, i) => (
          <button
            key={s.image}
            type="button"
            role="tab"
            className={`hero-banner__dot${i === active ? ' is-active' : ''}`}
            aria-selected={i === active}
            aria-label={`Diapositiva ${i + 1}: ${s.title}`}
            onClick={() => setActive(i)}
          />
        ))}
      </div>

      <a href="#confianza" className="hero-banner__scroll" aria-label="Ver más contenido">
        <span>Descubre más</span>
        <ChevronDown size={22} strokeWidth={2.5} aria-hidden />
      </a>

      <HomeHeroWave />
    </section>
  );
}
