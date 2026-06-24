import { useEffect, useRef, useState } from 'react';

function useCountUp(target: number, duration = 1400, active = false) {
  const [count, setCount] = useState(0);

  useEffect(() => {
    if (!active || target <= 0) {
      setCount(active ? target : 0);
      return;
    }
    let start = 0;
    const step = Math.max(1, Math.ceil(target / (duration / 16)));
    const timer = setInterval(() => {
      start += step;
      if (start >= target) {
        setCount(target);
        clearInterval(timer);
      } else {
        setCount(start);
      }
    }, 16);
    return () => clearInterval(timer);
  }, [target, duration, active]);

  return count;
}

export default function StatCounter({
  value,
  label,
  onDark = false,
}: {
  value: number;
  label: string;
  onDark?: boolean;
}) {
  const ref = useRef<HTMLDivElement>(null);
  const [active, setActive] = useState(false);
  const count = useCountUp(value, 1400, active);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const obs = new IntersectionObserver(([e]) => {
      if (e.isIntersecting) setActive(true);
    }, { threshold: 0.5 });
    obs.observe(el);
    return () => obs.disconnect();
  }, []);

  return (
    <div ref={ref} className="text-center">
      <p className={`font-display font-bold ${onDark ? 'text-[#6ECFC8]' : 'text-accent'} ${onDark ? 'text-5xl md:text-6xl' : 'text-5xl'}`}>{count}</p>
      <p
        className={`mt-1 text-sm font-medium uppercase tracking-widest ${
          onDark ? 'text-white/75' : 'text-muted-foreground'
        }`}
      >
        {label}
      </p>
    </div>
  );
}
