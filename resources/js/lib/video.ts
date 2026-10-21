export interface ResolvedVideo {
    kind: 'embed' | 'file'
    src: string
}

/**
 * Turn a stored video URL into something the browser can play.
 *
 * Supported sources: YouTube, Vimeo, and direct file URLs
 * (mp4, webm, ogg, mov). Returns null when the URL is empty or
 * uses an unsupported provider.
 */
export function resolveVideo(url: string | null | undefined): ResolvedVideo | null {
    if (!url) {
        return null
    }

    const youtube = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{6,})/)
    if (youtube) {
        return { kind: 'embed', src: `https://www.youtube.com/embed/${youtube[1]}` }
    }

    const vimeo = url.match(/vimeo\.com\/(\d+)/)
    if (vimeo) {
        return { kind: 'embed', src: `https://player.vimeo.com/video/${vimeo[1]}` }
    }

    if (/\.(mp4|webm|ogg|mov)(\?.*)?$/i.test(url)) {
        return { kind: 'file', src: url }
    }

    return null
}
