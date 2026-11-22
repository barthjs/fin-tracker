/** HTTP status codes a reverse proxy answers with while the server behind it does not answer. */
const GATEWAY_STATUSES: ReadonlySet<number> = new Set([502, 503, 504]);

/** Reports whether a status is the answer of a reverse proxy whose server does not answer. */
export function isGatewayError(status: number): boolean {
    return GATEWAY_STATUSES.has(status);
}
