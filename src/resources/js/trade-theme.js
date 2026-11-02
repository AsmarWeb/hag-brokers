// Compatibility reader only. Public theme controls live on the homepage.
// No independent storage key or toggle is provided here.
export default function tradeTheme() {
    return { get dark() { return document.documentElement.classList.contains('dark'); } };
}
