import React from 'react';

class Header extends React.Component {
    
    render () {

        return ( 
            
            <>     

                <div className="py-16 bg-white og-header-container">
                    <div className="container m-auto px-3 space-y-8 text-gray-500 md:px-3 lg:px-3 og-header-container-outer">
                        <div className="justify-center text-center gap-6 md:text-left md:flex lg:items-center lg:gap-16 og-header-container-inner-inner">
                            <div className="order-last mb-6 space-y-6 md:mb-0 md:w-6/12 lg:w-6/12 og-header-container-inner-right">
                                <h1 className="text-4xl text-gray-700 font-bold md:text-5xl og-header-container-inner-right-h1">
                                    { window.ogSettings.og_heading }
                                </h1>
                                <p className="text-lg og-header-container-inner-right-p">
                                    { window.ogSettings.og_subheading }
                                </p>
                                
                                <div className="justify-center gap-4 md:gap-6 md:justify-end w-full text-center">
                                    
                                        <a href={window.ogSettings.og_hp_btn_url}>
                                            <button type="button" title={window.ogSettings.og_hp_btn_text} className="w-full pt-2 pb-2 px-6 text-center rounded-xl transition bg-gray-800 shadow-xl hover:bg-gray-600 sm:w-max inline-block">
                                                <span className="block text-white font-semibold pt-px pb-px">
                                                    {window.ogSettings.og_hp_btn_text}
                                                </span>
                                            </button>
                                        </a>                                    
                                </div>
                            </div>
                            
                            <div className="grid grid-cols-5 grid-rows-4 gap-4 md:w-5/12 lg:w-6/12 og-header-container-inner-left">
                                <div className="col-span-2 row-span-4">
                                    <img src={window.ogSettings.og_design_image_1} alt="" className="rounded-full min-h-[350px] mt-1" width="640" height="960" loading="lazy" />
                                </div>
                                <div className="col-span-2 row-span-2">
                                    <img src={window.ogSettings.og_design_image_2} alt="" className="w-full h-full object-cover object-top rounded-xl" width="640" height="640" loading="lazy" />
                                </div>
                                <div className="col-span-3 row-span-3">
                                    <img src={window.ogSettings.og_design_image_3} alt="" className="w-full h-full object-cover object-top rounded-xl" width="640" height="427" loading="lazy" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </>

        );
    }   
}

export default Header;