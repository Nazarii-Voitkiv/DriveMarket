import type { Metadata } from "next";
import { Geist } from "next/font/google";
import "./globals.css";
import Script from 'next/script';

const geist = Geist({
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "Task Board",
  description: "Task Board Telegram App",
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <body className={geist.className}>
        <Script
          src="https://telegram.org/js/telegram-web-app.js"
          strategy="afterInteractive"
        />
        {children}
      </body>
    </html>
  );
}
