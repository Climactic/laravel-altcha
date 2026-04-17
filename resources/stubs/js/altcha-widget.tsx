import { useEffect } from "react";

type Props = {
    challengeUrl: string;
    auto?: "onload" | "onsubmit" | "off";
    floating?: "auto" | "top" | "bottom";
    hideFooter?: boolean;
    name?: string;
};

export function AltchaWidget({
    challengeUrl,
    auto = "onsubmit",
    floating = "bottom",
    hideFooter = true,
    name = "altcha",
}: Props) {
    useEffect(() => {
        import("altcha");
    }, []);

    return (
        <altcha-widget
            auto={auto}
            challengeurl={challengeUrl}
            floating={floating}
            hidefooter={hideFooter}
            name={name}
        />
    );
}
