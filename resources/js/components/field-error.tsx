export function FieldError({ error }: { error?: string }) {
    if (!error) {
        return null;
    }

    return (
        <p
            role="alert"
            className="mt-2 max-w-full text-sm break-words text-red-600 sm:text-[13px]"
        >
            {error}
        </p>
    );
}
