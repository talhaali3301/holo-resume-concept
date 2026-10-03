import { Vector3 } from 'three'
import { useTres, useLoop } from '@tresjs/core'

export interface PoseTarget {
    position: Vector3
    look: Vector3
}

export function useCameraRig(readPose: (target: PoseTarget) => void, motion: () => boolean, pulse?: (elapsed: number, moving: boolean) => void): void {
    const { camera, invalidate } = useTres()
    const { onBeforeRender } = useLoop()
    const position = new Vector3()
    const look = new Vector3()
    const currentLook = new Vector3()
    let primed = false

    onBeforeRender(({ elapsed }) => {
        const rig = camera.value

        if (!rig) {
            return
        }

        readPose({ position, look })
        const moving = motion()

        if (!primed || !moving) {
            const shifted = !primed || rig.position.distanceTo(position) > 0.004 || currentLook.distanceTo(look) > 0.004

            if (shifted) {
                rig.position.copy(position)
                currentLook.copy(look)
                rig.lookAt(currentLook)
                invalidate()
            }

            primed = true
            pulse?.(elapsed, false)

            return
        }

        rig.position.lerp(position, 0.08)
        currentLook.lerp(look, 0.08)
        rig.lookAt(currentLook)
        pulse?.(elapsed, true)
        invalidate()
    })
}
