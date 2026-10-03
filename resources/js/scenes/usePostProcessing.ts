import { Vector2, type Camera, type Scene, type WebGLRenderer } from 'three'
import { EffectComposer } from 'three/addons/postprocessing/EffectComposer.js'
import { OutputPass } from 'three/addons/postprocessing/OutputPass.js'
import { RenderPass } from 'three/addons/postprocessing/RenderPass.js'
import { UnrealBloomPass } from 'three/addons/postprocessing/UnrealBloomPass.js'

export interface BloomOptions {
    threshold?: number
    strength?: number
    radius?: number
}

export interface BloomPipeline {
    bloom: UnrealBloomPass
    render: () => void
    setSize: (width: number, height: number) => void
    dispose: () => void
}

/**
 * Only emissive/bright surfaces should bloom; everything else (and all DOM
 * text/UI, which never touches the canvas) stays crisp. UnrealBloomPass's
 * threshold does that selection for us.
 */
export function createBloomPipeline(
    renderer: WebGLRenderer,
    scene: Scene,
    camera: Camera,
    width: number,
    height: number,
    options: BloomOptions = {},
): BloomPipeline {
    const composer = new EffectComposer(renderer)
    composer.addPass(new RenderPass(scene, camera))

    const bloom = new UnrealBloomPass(new Vector2(width, height), options.strength ?? 1, options.radius ?? 0.85, options.threshold ?? 0.65)
    composer.addPass(bloom)
    composer.addPass(new OutputPass())
    composer.setSize(width, height)

    return {
        bloom,
        render: () => composer.render(),
        setSize: (nextWidth, nextHeight) => composer.setSize(nextWidth, nextHeight),
        dispose: () => composer.dispose(),
    }
}
