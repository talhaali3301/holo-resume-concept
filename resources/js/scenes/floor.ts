import { Mesh, PlaneGeometry, ShaderMaterial } from 'three'

const vertexShader = `
varying vec2 vUv;
void main() {
  vUv = uv;
  gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
}
`

const fragmentShader = `
varying vec2 vUv;
uniform float uTime;
uniform float uMotion;
void main() {
  vec2 gridUv = vUv * vec2(28.0, 18.0);
  vec2 fw = max(fwidth(gridUv), vec2(0.001));
  vec2 grid = abs(fract(gridUv - 0.5) - 0.5) / fw;
  float line = 1.0 - min(min(grid.x, grid.y), 1.0);
  line *= smoothstep(0.22, 0.04, max(fw.x, fw.y));
  float fade = smoothstep(0.04, 0.18, vUv.y) * (1.0 - smoothstep(0.48, 0.78, vUv.y));
  fade *= smoothstep(0.0, 0.08, vUv.x) * (1.0 - smoothstep(0.92, 1.0, vUv.x));
  float pulse = uMotion > 0.5 ? 0.82 + 0.18 * sin(uTime * 0.7) : 0.9;
  vec3 base = vec3(0.027, 0.04, 0.07);
  vec3 glow = vec3(0.48, 0.86, 0.96);
  vec3 color = mix(base, glow, line * 0.45 * fade * pulse);
  gl_FragColor = vec4(color, 1.0);
}
`

export function createFloor(width: number, depth: number): { mesh: Mesh; material: ShaderMaterial } {
    const material = new ShaderMaterial({
        uniforms: {
            uTime: { value: 0 },
            uMotion: { value: 0 },
        },
        vertexShader,
        fragmentShader,
    })
    const mesh = new Mesh(new PlaneGeometry(width, depth), material)
    mesh.rotation.x = -Math.PI / 2

    return { mesh, material }
}
