import { AdditiveBlending, Color, Mesh, PlaneGeometry, ShaderMaterial } from 'three'

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
uniform float uIntensity;
void main() {
  float vertical = smoothstep(0.0, 0.75, vUv.y);
  float edge = smoothstep(0.0, 0.12, vUv.x) * (1.0 - smoothstep(0.88, 1.0, vUv.x));
  float alpha = vertical * edge * uIntensity;
  gl_FragColor = vec4(uColor, alpha);
}
`

/**
 * A vertical light gradient for a gateway's interior: additive, fading out
 * toward the floor, never a flat filled rectangle.
 */
export function createGatewayGlow(width: number, height: number, color: string, intensity = 0.55): Mesh {
    const material = new ShaderMaterial({
        uniforms: {
            uColor: { value: new Color(color) },
            uIntensity: { value: intensity },
        },
        vertexShader,
        fragmentShader,
        transparent: true,
        depthWrite: false,
        blending: AdditiveBlending,
    })
    const mesh = new Mesh(new PlaneGeometry(width, height), material)
    mesh.geometry.translate(0, height / 2, 0)

    return mesh
}
