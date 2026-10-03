import { Group, Mesh, PlaneGeometry, ShaderMaterial } from 'three'
import { Reflector } from 'three/addons/objects/Reflector.js'

const gridVertexShader = `
varying vec2 vUv;
void main() {
  vUv = uv;
  gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
}
`

const gridFragmentShader = `
varying vec2 vUv;
uniform float uTime;
uniform float uMotion;
void main() {
  vec2 gridUv = vUv * vec2(28.0, 18.0);
  vec2 fw = max(fwidth(gridUv), vec2(0.001));
  vec2 grid = abs(fract(gridUv - 0.5) - 0.5) / fw;
  float line = 1.0 - min(min(grid.x, grid.y), 1.0);
  line *= smoothstep(0.22, 0.04, max(fw.x, fw.y));
  float distanceFade = 1.0 - smoothstep(0.0, 0.85, vUv.y);
  float edgeFade = smoothstep(0.0, 0.1, vUv.x) * (1.0 - smoothstep(0.9, 1.0, vUv.x));
  float pulse = uMotion > 0.5 ? 0.85 + 0.15 * sin(uTime * 0.7) : 1.0;
  float alpha = line * distanceFade * edgeFade * pulse * 0.08;
  vec3 glow = vec3(0.37, 0.9, 1.0);
  gl_FragColor = vec4(glow, alpha);
}
`

export interface SceneFloor {
    group: Group
    material: ShaderMaterial
    reflector: Reflector
    dispose: () => void
}

export function createFloor(width: number, depth: number): SceneFloor {
    const group = new Group()

    const reflector = new Reflector(new PlaneGeometry(width, depth), {
        color: 0x15202f,
        textureWidth: 1024,
        textureHeight: 1024,
        clipBias: 0.0005,
    })
    reflector.rotation.x = -Math.PI / 2
    group.add(reflector)

    const material = new ShaderMaterial({
        uniforms: {
            uTime: { value: 0 },
            uMotion: { value: 0 },
        },
        vertexShader: gridVertexShader,
        fragmentShader: gridFragmentShader,
        transparent: true,
        depthWrite: false,
    })
    const grid = new Mesh(new PlaneGeometry(width, depth), material)
    grid.rotation.x = -Math.PI / 2
    grid.position.y = 0.003
    group.add(grid)

    return {
        group,
        material,
        reflector,
        dispose: () => {
            reflector.geometry.dispose()
            ;(reflector.material as { dispose: () => void }).dispose()
            grid.geometry.dispose()
            material.dispose()
        },
    }
}
