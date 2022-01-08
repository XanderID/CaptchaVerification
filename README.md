# General

this plugin can help to verify bot or human
for Pocketmine 4.0.0

# Pre-Requires
- Php Gd Image Library
- [ImageOnMap](https://poggit.pmmp.io/p/ImageOnMap/1.0.0)

# Screenshot

![Screenshot](https://github.com/MulqiGaming64/CaptchaVerification/blob/170a988a456112e57f53d47943fe44bf14534808/Screenshot.png)

# Feature
- Captcha code are Random Generate
- can set max attempt
- Captcha in Image Map
- Cancel move / cmd if you haven't written the captcha correctly
- can set background color
- can set text color
- can set session time for captcha code
- can set if attempt reach max regenerate new or kick

# To-Do
- Max Time to Input code
- do you have any ideas?

# Config

``` YAML

---
# Text Captcha Color
# Can use § or & or TextFormat ( GRAY etc )
text-color: "§f"

# Background Captcha Color
# Can use § or & or TextFormat ( GRAY etc )
background-color: "§b"

# Add Number in Captcha?
number: true

# maximum attempt when captcha
max-attempt: 3

# Mode if the Attempt reach max
# Mode: kick ( if there are too many wrong captcha code )
# Mode: regenerate ( Regenerate new Captcha code when many wrong captcha code )
mode: "regenerate"

# Message when kick
kick: "§cyou have tried many captcha code but it's wrong, please join again"

# Message when regenerate
regenerate: "§cyou have tried many captcha code but it's wrong, \n§aregenerating new code please wait 3 seconds"

# cancel move if the player has not written the captcha?
cancel-move: true

# can command? if the player has not written the captcha?
can-command: false

# Add Captcha Only on First Joined?
# if true then session time will be disabled
first-join: false

# Session time for players to enter captcha again
# when join
# In Seconds ( 3600 is 1 Hours )
session: 3600

# Execute cmd Message
cmd-message: "§cPlease write captcha before run a commands!"

# Input code Message
input-code: "§aPlease write down the captcha in your hand \n §aTo identify you as a bot or not"

# Wrong code Message
wrong-code: "§cCaptcha code is Wrong! try again!"

# Correct Code Message
correct-code: "you have entered the correct captcha code!, happy playing"

# Tip message
tip-message: "§cplease complete the captcha"
...
```

# Additional Notes

- If you find bugs or want to give suggestions, please visit [here](https://github.com/MulqiGaming64/CaptchaVerification/issues)
- Icons By [flaticon.com](https://www.flaticon.com)
