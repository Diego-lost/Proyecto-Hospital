/**
 * Llama DxGPT /api/diagnose y espera el resultado por Azure Web PubSub.
 * Uso: node dxgpt-diagnose.mjs '<json payload fields>'
 * Imprime JSON del diagnóstico o error en stderr y exit 1.
 */
import WebSocket from 'ws';
import { randomUUID } from 'crypto';
import { readFileSync } from 'fs';

const base = process.env.DXGPT_BASE_URL || 'https://dxgpt-apim.azure-api.net';
const key = process.env.DXGPT_SUBSCRIPTION_KEY;
const descriptionArg = process.argv[2] || '';
const description = descriptionArg.startsWith('@')
  ? readFileSync(descriptionArg.slice(1), 'utf8')
  : descriptionArg;
const model = process.env.DXGPT_MODEL || 'gpt4o';
const myuuid = randomUUID();
const timezone = process.env.DXGPT_TIMEZONE || 'America/Lima';

if (!key) {
  console.error('Missing DXGPT_SUBSCRIPTION_KEY');
  process.exit(1);
}

const headers = {
  'Content-Type': 'application/json',
  'Ocp-Apim-Subscription-Key': key,
};

async function postJson(path, body) {
  const res = await fetch(`${base}${path}`, {
    method: 'POST',
    headers,
    body: JSON.stringify(body),
  });
  const text = await res.text();
  let json;
  try {
    json = JSON.parse(text);
  } catch {
    json = { raw: text };
  }
  return { status: res.status, json };
}

function waitForDiagnosis(wsUrl, groupId, timeoutMs) {
  return new Promise((resolve, reject) => {
    const ws = new WebSocket(wsUrl, 'json.webpubsub.azure.v1');
    let done = false;
    const timer = setTimeout(() => {
      if (!done) {
        done = true;
        ws.close();
        reject(new Error('WebPubSub timeout'));
      }
    }, timeoutMs);

    ws.on('open', () => {
      ws.send(JSON.stringify({ type: 'joinGroup', group: groupId, ackId: 1 }));
    });

    ws.on('message', (raw) => {
      let msg;
      try {
        msg = JSON.parse(String(raw));
      } catch {
        return;
      }

      if (msg.type !== 'message' || !msg.data) {
        return;
      }

      const payload = typeof msg.data === 'string' ? tryParse(msg.data) : msg.data;

      if (payload?.type === 'result' && payload.status === 'success' && payload.data) {
        if (!done) {
          done = true;
          clearTimeout(timer);
          ws.close();
          resolve(payload.data);
        }
        return;
      }

      if (payload?.type === 'complete' && payload.result) {
        if (!done) {
          done = true;
          clearTimeout(timer);
          ws.close();
          resolve(payload.result);
        }
        return;
      }

      if (payload?.type === 'error') {
        if (!done) {
          done = true;
          clearTimeout(timer);
          ws.close();
          reject(new Error(payload.error || payload.message || 'DxGPT WebPubSub error'));
        }
      }
    });

    ws.on('error', (err) => {
      if (!done) {
        done = true;
        clearTimeout(timer);
        reject(err);
      }
    });
  });
}

function tryParse(s) {
  try {
    return JSON.parse(s);
  } catch {
    return s;
  }
}

const negotiate = await postJson('/api/pubsub/negotiate', { myuuid, userId: myuuid });
if (negotiate.status !== 200 || !negotiate.json?.url) {
  console.error(JSON.stringify({ step: 'negotiate', status: negotiate.status, body: negotiate.json }));
  process.exit(1);
}

const wsPromise = waitForDiagnosis(negotiate.json.url, myuuid, 90000);

const diagnose = await postJson('/api/diagnose', {
  description,
  model,
  myuuid,
  timezone,
  lang: 'es',
});

if (diagnose.status >= 400) {
  console.error(JSON.stringify({ step: 'diagnose', status: diagnose.status, body: diagnose.json }));
  process.exit(1);
}

const result = await wsPromise;
console.log(JSON.stringify(result));
