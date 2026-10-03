import { Color, ShaderMaterial } from 'three'

const vertexShader = `
varying vec2 vUv;
void main() {
  vUv = uv;
  gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
}
`

const fragmentShader = `
varying vec2 vUv;
uniform vec3 uColor;
uniform float uTime;
uniform float uLive;
void main() {
  float scan = 0.74 + 0.26 * sin(vUv.y * 48.0 - uTime * (uLive > 0.5 ? 1.5 : 0.0));
  float mask = smoothstep(0.0, 0.06, vUv.x) * (1.0 - smoothstep(0.94, 1.0, vUv.x));
  mask *= smoothstep(0.0, 0.08, vUv.y) * (1.0 - smoothstep(0.9, 1.0, vUv.y));
  float sweep = smoothstep(0.0, 0.2, fract(uTime * 0.08 + vUv.y));
  vec3 color = uColor * (0.16 + 0.5 * vUv.y) * scan * mask;
  color += uColor * sweep * 0.08 * uLive;
  gl_FragColor = vec4(color, 1.0);
}
`

export const frameAccents = ['#8eecff', '#6aa6ff', '#d7e7ff', '#7ad7ff', '#9eb6ff', '#b9f6ff']

export function createScreen(hex: string): ShaderMaterial {
    return new ShaderMaterial({
        uniforms: {
            uColor: { value: new Color(hex) },
            uTime: { value: 0 },
            uLive: { value: 0 },
        },
        vertexShader,
        fragmentShader,
    })
}
