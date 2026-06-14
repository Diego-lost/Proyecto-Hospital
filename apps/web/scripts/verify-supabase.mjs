/**
 * Prueba URL + clave publicable/anon: lectura de catálogo e insert de prueba en solicitudes_citas.
 * Uso: npm run verify:supabase --prefix apps/web
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { createClient } from '@supabase/supabase-js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const envPath = path.join(__dirname, '..', '.env');

function loadEnv(file) {
  const out = {};
  if (!fs.existsSync(file)) return out;
  for (const line of fs.readFileSync(file, 'utf8').split('\n')) {
    const t = line.trim();
    if (!t || t.startsWith('#')) continue;
    const i = t.indexOf('=');
    if (i === -1) continue;
    const k = t.slice(0, i).trim();
    let v = t.slice(i + 1).trim();
    if (
      (v.startsWith('"') && v.endsWith('"')) ||
      (v.startsWith("'") && v.endsWith("'"))
    ) {
      v = v.slice(1, -1);
    }
    out[k] = v;
  }
  return out;
}

const env = loadEnv(envPath);
const url = String(env.VITE_SUPABASE_URL ?? '').trim();
const key = String(env.VITE_SUPABASE_ANON_KEY ?? '').trim();

if (!url || !key) {
  console.error('Falta VITE_SUPABASE_URL o VITE_SUPABASE_ANON_KEY en apps/web/.env');
  process.exit(1);
}

const sb = createClient(url, key);

const { error: readErr } = await sb.from('especialidades').select('id').limit(1);
if (readErr) {
  console.error('Lectura (especialidades):', readErr.message);
  process.exit(1);
}
console.log('OK lectura: especialidades accesible.');

const { error: insErr } = await sb.from('solicitudes_citas').insert({
  nombre: '__verificacion_script__',
  telefono: '000000000',
  origen: 'verify-supabase.mjs',
  estado: 'nueva',
});
if (insErr) {
  console.error('Guardar (solicitudes_citas):', insErr.message);
  process.exit(1);
}
console.log('OK guardado: fila de prueba insertada. Puedes borrarla en Table Editor → solicitudes_citas (nombre __verificacion_script__).');
