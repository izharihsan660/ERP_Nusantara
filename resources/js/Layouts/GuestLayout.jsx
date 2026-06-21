export default function GuestLayout({ children }) {
    return (
        <div className="min-h-screen bg-[hsl(var(--background))] text-[hsl(var(--foreground))]">
            {children}
        </div>
    );
}
