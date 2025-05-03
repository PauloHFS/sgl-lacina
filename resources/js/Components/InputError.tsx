export default function InputError({
    message,
    className = '',
}: {
    message?: string;
    className?: string;
}) {
    return message ? (
        <p className={`text-error mt-1 text-xs ${className}`}>{message}</p>
    ) : null;
}
